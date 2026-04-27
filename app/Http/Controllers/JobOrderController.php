<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobOrderRequest;
use App\Http\Requests\UpdateJobOrderRequest;
use App\Models\Customer;
use App\Models\JobCalculation;
use App\Models\JobOrder;
use App\Models\JobPayment;
use App\Models\PaperStock;
use App\Models\PaperType;
use App\Models\Tenant;
use App\Services\PrintCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JobOrderController extends Controller
{
    public function __construct(private readonly PrintCalculationService $calculationService)
    {
        $this->authorizeResource(JobOrder::class, 'job_order');
    }

    public function index(): View
    {
        $jobOrders = JobOrder::with(['customer', 'paperType'])->latest()->paginate(20);

        return view('job-orders.index', compact('jobOrders'));
    }

    public function create(): View
    {
        return view('job-orders.create', [
            'customers' => Customer::orderBy('company_name')->get(),
            'paperTypes' => PaperType::orderBy('name')->get(),
            'settings' => config('printing'),
        ]);
    }

    public function store(StoreJobOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenantId = Tenant::query()->value('id');

        DB::transaction(function () use ($request, $data, $tenantId): void {
            $calculation = $this->calculationService->calculate([
                'total_pages' => $data['total_pages'],
                'page_size' => $data['page_size'],
                'custom_width' => $data['custom_width'] ?? null,
                'custom_height' => $data['custom_height'] ?? null,
                'total_copies' => $data['total_copies'],
                'standard_sheet_size' => $data['standard_sheet_size'],
                'colors' => $data['colors'],
                'wastage_per_color' => config('printing.wastage.default_per_color_percent'),
                'printing_style' => $data['printing_style'],
            ]);

            $jobOrder = JobOrder::create(array_merge($data, [
                'tenant_id' => $tenantId,
                'created_by' => $request->user()?->id,
                'estimated_total_cost' => ($data['estimated_material_cost'] ?? 0)
                    + ($data['estimated_other_cost'] ?? 0),
                'estimated_total_price' => ((float) ($data['estimated_unit_price'] ?? 0)) * (float) $data['total_copies'],
            ]));

            JobCalculation::create([
                'job_order_id' => $jobOrder->id,
                'pages_per_sheet' => $calculation['pages_per_sheet'],
                'raw_sheets' => $calculation['raw_sheets'],
                'wastage_percentage' => $calculation['wastage_percentage'],
                'wastage_sheets' => $calculation['wastage_sheets'],
                'total_sheets' => $calculation['total_sheets'],
                'reams' => $calculation['reams'],
                'quires' => $calculation['quires'],
                'remainder_sheets' => $calculation['remainder_sheets'],
                'input_snapshot' => $data,
                'computed_at' => now(),
            ]);
        });

        return redirect()->route('job-orders.index')->with('success', 'Job order created successfully.');
    }

    public function show(JobOrder $jobOrder): View
    {
        $jobOrder->load(['customer', 'paperType', 'payments', 'plates', 'deliveryChallans', 'calculation']);

        return view('job-orders.show', compact('jobOrder'));
    }

    public function edit(JobOrder $jobOrder): View
    {
        return view('job-orders.edit', [
            'jobOrder' => $jobOrder,
            'customers' => Customer::orderBy('company_name')->get(),
            'paperTypes' => PaperType::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateJobOrderRequest $request, JobOrder $jobOrder): RedirectResponse
    {
        $jobOrder->update($request->validated());

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Job order updated successfully.');
    }

    public function destroy(JobOrder $jobOrder): RedirectResponse
    {
        $jobOrder->delete();

        return redirect()->route('job-orders.index')->with('success', 'Job order deleted successfully.');
    }

    public function updateStatus(Request $request, JobOrder $jobOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,confirmed,in_production,quality_check,delivered'],
        ]);

        if ($validated['status'] === 'in_production') {
            $this->ensureAdvancePaymentCompliance($jobOrder);
            $this->deductPaperStock($jobOrder);
        }

        $jobOrder->update(['status' => $validated['status']]);

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Status updated successfully.');
    }

    public function previewCalculation(Request $request)
    {
        $payload = $request->validate([
            'total_pages' => ['required', 'integer', 'min:1'],
            'page_size' => ['required', 'string'],
            'custom_width' => ['nullable', 'numeric', 'min:0.1'],
            'custom_height' => ['nullable', 'numeric', 'min:0.1'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'standard_sheet_size' => ['required', 'in:demy,crown,double_crown,royal'],
            'colors' => ['required', 'integer', 'min:1', 'max:4'],
            'printing_style' => ['required', 'in:work_and_turn,work_and_back'],
        ]);

        return response()->json($this->calculationService->calculate($payload));
    }

    public function jobCardPdf(JobOrder $jobOrder)
    {
        $jobOrder->load(['customer', 'paperType', 'calculation', 'payments', 'plates']);
        $pdf = Pdf::loadView('job-orders.job-card-pdf', compact('jobOrder'));

        return $pdf->download('job-card-' . $jobOrder->job_number . '.pdf');
    }

    private function ensureAdvancePaymentCompliance(JobOrder $jobOrder): void
    {
        $requiredAdvance = (float) $jobOrder->estimated_total_cost * ((float) config('printing.advance_payment_percent', 50) / 100);
        $totalAdvance = (float) JobPayment::query()
            ->where('job_order_id', $jobOrder->id)
            ->where('payment_stage', 'advance')
            ->sum('amount');

        if ($totalAdvance + 0.0001 < $requiredAdvance) {
            abort(422, 'Cannot move to production. Minimum 50% advance payment is required.');
        }
    }

    private function deductPaperStock(JobOrder $jobOrder): void
    {
        $latestCalculation = $jobOrder->calculation()->latest('computed_at')->first();

        if (! $latestCalculation) {
            abort(422, 'No print calculation found for this job.');
        }

        $paperStock = PaperStock::query()
            ->where('tenant_id', $jobOrder->tenant_id)
            ->where('paper_type_id', $jobOrder->paper_type_id)
            ->where('gsm', $jobOrder->gsm)
            ->where('sheet_size', $jobOrder->standard_sheet_size)
            ->lockForUpdate()
            ->first();

        if (! $paperStock) {
            abort(422, 'No paper stock record found for this job spec.');
        }

        $requiredSheets = (int) $latestCalculation->total_sheets;

        if ($paperStock->available_sheets < $requiredSheets) {
            $shortfall = $requiredSheets - $paperStock->available_sheets;
            abort(422, 'Insufficient paper stock. Shortfall: ' . $shortfall . ' sheets.');
        }

        $remainingSheets = $paperStock->available_sheets - $requiredSheets;
        $paperStock->update([
            'stock_reams' => intdiv($remainingSheets, 500),
            'stock_quires' => intdiv($remainingSheets % 500, 25),
            'stock_sheets' => $remainingSheets % 25,
        ]);
    }
}
