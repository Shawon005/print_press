<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InkType;
use App\Models\JobCalculation;
use App\Models\JobOrder;
use App\Models\JobPayment;
use App\Models\Order;
use App\Models\PaperStock;
use App\Models\PaperType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder as JobPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialCategory;
use App\Models\Setting;
use App\Models\StandardSheet;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use App\Services\PrintCalculationService;

class ModuleRecordController extends Controller
{
    public function __construct(private readonly PrintCalculationService $printCalculationService)
    {
    }

    public function create(string $module): View
    {
        if ($module === 'orders') {
            return view('modules.orders-form', [
                'module' => $module,
                'customers' => Customer::orderBy('company_name')->get(),
                'paperTypes' => PaperType::orderBy('name')->get(),
                'inkTypes' => InkType::where('tenant_id', Tenant::firstOrFail()->id)->orderBy('name')->get(),
                'standardSheets' => StandardSheet::where('tenant_id', Tenant::firstOrFail()->id)->orderBy('name')->get(),
                'record' => null,
                'formAction' => route('modules.store', $module),
                'formMethod' => 'POST',
            ]);
        }
        if ($module === 'quotations') {
            return view('modules.quotations-form', [
                'module' => $module,
                'customers' => Customer::orderBy('company_name')->get(),
                'record' => null,
                'items' => [],
                'formAction' => route('modules.store', $module),
                'formMethod' => 'POST',
            ]);
        }

        $config = $this->config($module);
        abort_unless($config, 404);

        return view('modules.create', [
            'module' => $module,
            'config' => $config,
            'options' => $this->options(),
            'record' => null,
            'formAction' => route('modules.store', $module),
            'formMethod' => 'POST',
        ]);
    }

    public function edit(string $module, int $id): View
    {
        if ($module === 'orders') {
            $record = JobOrder::findOrFail($id);

            return view('modules.orders-form', [
                'module' => $module,
                'customers' => Customer::orderBy('company_name')->get(),
                'paperTypes' => PaperType::orderBy('name')->get(),
                'inkTypes' => InkType::where('tenant_id', Tenant::firstOrFail()->id)->orderBy('name')->get(),
                'standardSheets' => StandardSheet::where('tenant_id', Tenant::firstOrFail()->id)->orderBy('name')->get(),
                'record' => $record,
                'formAction' => route('modules.update', [$module, $id]),
                'formMethod' => 'PUT',
            ]);
        }
        if ($module === 'quotations') {
            $record = Quotation::with('items')->findOrFail($id);

            return view('modules.quotations-form', [
                'module' => $module,
                'customers' => Customer::orderBy('company_name')->get(),
                'record' => $record,
                'items' => $record->items->map(fn (QuotationItem $item) => [
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ])->all(),
                'formAction' => route('modules.update', [$module, $id]),
                'formMethod' => 'PUT',
            ]);
        }

        $config = $this->config($module);
        abort_unless($config, 404);

        $record = $config['model']::findOrFail($id);

        return view('modules.create', [
            'module' => $module,
            'config' => array_merge($config, ['title' => 'Edit ' . rtrim($config['title'], 'e')]),
            'options' => $this->options(),
            'record' => $record,
            'formAction' => route('modules.update', [$module, $id]),
            'formMethod' => 'PUT',
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        if ($module === 'orders') {
            $tenant = $this->tenant();
            $user = $request->user();

            $data = $request->validate($this->printingOrderRules());

            DB::transaction(function () use ($data, $tenant, $user): void {
                $calculation = $this->printCalculationService->calculate([
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

                $order = JobOrder::create([
                    'tenant_id' => $tenant->id,
                    'job_number' => $data['job_number'],
                    'job_title' => $data['job_title'],
                    'customer_id' => $data['customer_id'],
                    'created_by' => $user?->id,
                    'order_date' => $data['order_date'] ?? now()->toDateString(),
                    'due_date' => $data['due_date'] ?? null,
                    'status' => $data['status'] ?? 'draft',
                    'gsm' => $data['gsm'],
                    'paper_type_id' => $data['paper_type_id'],
                    'ink_type' => $data['ink_type'],
                    'pantone_codes' => $data['pantone_codes'] ?? null,
                    'finish_type' => $data['finish_type'],
                    'total_pages' => $data['total_pages'],
                    'page_size' => $data['page_size'],
                    'custom_width' => $data['custom_width'] ?? null,
                    'custom_height' => $data['custom_height'] ?? null,
                    'total_copies' => $data['total_copies'],
                    'standard_sheet_size' => $data['standard_sheet_size'],
                    'colors' => $data['colors'],
                    'printing_style' => $data['printing_style'],
                    'estimated_material_cost' => $data['estimated_material_cost'] ?? 0,
                    'estimated_other_cost' => $data['estimated_other_cost'] ?? 0,
                    'estimated_plate_cost' => 0,
                    'estimated_total_cost' => ($data['estimated_material_cost'] ?? 0) + ($data['estimated_other_cost'] ?? 0),
                    'estimated_unit_price' => $data['estimated_unit_price'] ?? 0,
                    'estimated_total_price' => ($data['estimated_unit_price'] ?? 0) * $data['total_copies'],
                    'notes' => $data['notes'] ?? null,
                ]);

                JobCalculation::create([
                    'job_order_id' => $order->id,
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

            return redirect()->route('portal.page', ['page' => 'orders'])
                ->with('success', 'Printing order created successfully.');
        }
        if ($module === 'quotations') {
            $tenant = $this->tenant();
            $user = $request->user();
            $data = $request->validate($this->quotationRules());

            $quotation = DB::transaction(function () use ($data, $tenant, $user): Quotation {
                $subtotal = collect($data['items'])->sum(function (array $item): float {
                    return ((float) $item['quantity']) * ((float) $item['unit_price']);
                });
                $profitPercentage = (float) ($data['profit_percentage'] ?? 0);
                $profitAmount = $subtotal * ($profitPercentage / 100);
                $discount = (float) ($data['discount'] ?? 0);
                $tax = (float) ($data['tax'] ?? 0);
                $total = $subtotal + $profitAmount - $discount + $tax;

                $quotation = Quotation::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $data['customer_id'],
                    'quote_number' => $data['quote_number'],
                    'inquiry_date' => $data['inquiry_date'] ?? now()->toDateString(),
                    'valid_until' => $data['valid_until'] ?? now()->addDays(7)->toDateString(),
                    'status' => $data['status'] ?? 'draft',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'profit_percentage' => $profitPercentage,
                    'profit_amount' => $profitAmount,
                    'total' => $total,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $user?->id,
                    'approved_at' => ($data['status'] ?? null) === 'approved' ? now() : null,
                ]);

                $this->syncQuotationItems($quotation, $data['items']);

                return $quotation;
            });

            return redirect()->route('portal.page', ['page' => 'quotations'])
                ->with('success', 'Quotation created successfully: ' . $quotation->quote_number);
        }

        $config = $this->config($module);
        abort_unless($config, 404);

        $data = $request->validate($config['rules']);
        $tenant = $this->tenant();
        $user = $request->user();

        $payload = ($config['payload'])($data, $tenant, $user);
        $record = $config['model']::create($payload);
        if (isset($config['after_store'])) {
            $config['after_store']($record, $data);
        }

        return redirect()->route($this->redirectPage($module), $this->redirectParams($module))
            ->with('success', $config['success']);
    }

    public function update(Request $request, string $module, int $id): RedirectResponse
    {
        if ($module === 'orders') {
            $data = $request->validate($this->printingOrderRules());
            $record = JobOrder::findOrFail($id);

            DB::transaction(function () use ($data, $record): void {
                $calculation = $this->printCalculationService->calculate([
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

                $record->update([
                    'job_number' => $data['job_number'],
                    'job_title' => $data['job_title'],
                    'customer_id' => $data['customer_id'],
                    'order_date' => $data['order_date'] ?? now()->toDateString(),
                    'due_date' => $data['due_date'] ?? null,
                    'status' => $data['status'] ?? $record->status,
                    'gsm' => $data['gsm'],
                    'paper_type_id' => $data['paper_type_id'],
                    'ink_type' => $data['ink_type'],
                    'pantone_codes' => $data['pantone_codes'] ?? null,
                    'finish_type' => $data['finish_type'],
                    'total_pages' => $data['total_pages'],
                    'page_size' => $data['page_size'],
                    'custom_width' => $data['custom_width'] ?? null,
                    'custom_height' => $data['custom_height'] ?? null,
                    'total_copies' => $data['total_copies'],
                    'standard_sheet_size' => $data['standard_sheet_size'],
                    'colors' => $data['colors'],
                    'printing_style' => $data['printing_style'],
                    'estimated_material_cost' => $data['estimated_material_cost'] ?? 0,
                    'estimated_other_cost' => $data['estimated_other_cost'] ?? 0,
                    'estimated_total_cost' => ($data['estimated_material_cost'] ?? 0) + ($data['estimated_other_cost'] ?? 0) + (float) $record->estimated_plate_cost,
                    'estimated_unit_price' => $data['estimated_unit_price'] ?? 0,
                    'estimated_total_price' => ($data['estimated_unit_price'] ?? 0) * $data['total_copies'],
                    'notes' => $data['notes'] ?? null,
                ]);

                JobCalculation::updateOrCreate(
                    ['job_order_id' => $record->id],
                    [
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
                    ]
                );
            });

            return redirect()->route('portal.page', ['page' => 'orders'])
                ->with('success', 'Printing order updated successfully.');
        }
        if ($module === 'quotations') {
            $data = $request->validate($this->quotationRules());
            $quotation = Quotation::findOrFail($id);

            DB::transaction(function () use ($data, $quotation): void {
                $subtotal = collect($data['items'])->sum(function (array $item): float {
                    return ((float) $item['quantity']) * ((float) $item['unit_price']);
                });
                $profitPercentage = (float) ($data['profit_percentage'] ?? 0);
                $profitAmount = $subtotal * ($profitPercentage / 100);
                $discount = (float) ($data['discount'] ?? 0);
                $tax = (float) ($data['tax'] ?? 0);
                $total = $subtotal + $profitAmount - $discount + $tax;

                $quotation->update([
                    'customer_id' => $data['customer_id'],
                    'quote_number' => $data['quote_number'],
                    'inquiry_date' => $data['inquiry_date'] ?? now()->toDateString(),
                    'valid_until' => $data['valid_until'] ?? now()->addDays(7)->toDateString(),
                    'status' => $data['status'] ?? 'draft',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'profit_percentage' => $profitPercentage,
                    'profit_amount' => $profitAmount,
                    'total' => $total,
                    'notes' => $data['notes'] ?? null,
                    'approved_at' => ($data['status'] ?? null) === 'approved' ? now() : null,
                ]);

                $this->syncQuotationItems($quotation, $data['items'], true);
            });

            return redirect()->route('portal.page', ['page' => 'quotations'])
                ->with('success', 'Quotation updated successfully.');
        }

        $config = $this->config($module);
        abort_unless($config, 404);

        $data = $request->validate($config['rules']);
        $tenant = $this->tenant();
        $user = $request->user();
        $record = $config['model']::findOrFail($id);

        $payload = ($config['payload'])($data, $tenant, $user);
        $record->update($payload);
        if (isset($config['after_update'])) {
            $config['after_update']($record, $data);
        }

        return redirect()->route($this->redirectPage($module), $this->redirectParams($module))
            ->with('success', rtrim($config['success'], '.') . ' updated.');
    }

    public function destroy(string $module, int $id): RedirectResponse
    {
        if ($module === 'orders') {
            JobOrder::findOrFail($id)->delete();

            return redirect()->route('portal.page', ['page' => 'orders'])
                ->with('success', 'Printing order deleted successfully.');
        }
        if ($module === 'quotations') {
            $quotation = Quotation::findOrFail($id);
            $quotation->items()->delete();
            $quotation->delete();

            return redirect()->route('portal.page', ['page' => 'quotations'])
                ->with('success', 'Quotation deleted successfully.');
        }

        $config = $this->config($module);
        abort_unless($config, 404);

        $record = $config['model']::findOrFail($id);
        $record->delete();

        return redirect()->route($this->redirectPage($module), $this->redirectParams($module))
            ->with('success', str($module)->headline() . ' record deleted successfully.');
    }

    public function updateOrderStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $jobOrder = JobOrder::find($id);
        if ($jobOrder) {
            if ($data['status'] === 'in_production') {
                $this->enforceAdvancePaymentAndStock($jobOrder);
            }

            $jobOrder->update(['status' => $data['status']]);
        } else {
            $order = Order::findOrFail($id);
            $order->update(['status' => $data['status']]);
        }

        return redirect()->route('portal.page', ['page' => 'orders'])
            ->with('success', 'Order status updated successfully.');
    }

    public function export(string $module)
    {
        if ($module === 'orders') {
            $tenant = $this->tenant();
            $rows = array_merge([['Job Number', 'Customer ID', 'Job Title', 'Order Date', 'Due Date', 'Status', 'Estimated Total']], JobOrder::where('tenant_id', $tenant->id)->get(['job_number', 'customer_id', 'job_title', 'order_date', 'due_date', 'status', 'estimated_total_price'])->map(fn ($o) => [$o->job_number, $o->customer_id, $o->job_title, $o->order_date, $o->due_date, $o->status, $o->estimated_total_price])->all());

            $csv = collect($rows)->map(fn ($row) => collect($row)->map(function ($value) {
                $value = str_replace('"', '""', (string) $value);
                return '"' . $value . '"';
            })->implode(','))->implode("\n");

            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $module . '-export.csv"',
            ]);
        }

        $config = $this->config($module);
        abort_unless($config, 404);

        $tenant = $this->tenant();
        $rows = ($config['export'])($tenant);

        $csv = collect($rows)->map(fn ($row) => collect($row)->map(function ($value) {
            $value = str_replace('"', '""', (string) $value);
            return '"' . $value . '"';
        })->implode(','))->implode("\n");

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $module . '-export.csv"',
        ]);
    }

    public function print(string $module, int $id)
    {
        $tenant = $this->tenant();
        $payload = $this->printPayload($module, $id, $tenant->id);
        abort_unless($payload, 404);

        $pdf = Pdf::loadView($payload['view'], $payload['data']);

        return $pdf->download($payload['filename'] . '.pdf');
    }

    public function printBatchQuotations(Request $request)
    {
        $tenant = $this->tenant();
        if (count((array) $request->input('quotation_ids', [])) === 0) {
            return back()->with('error', 'Please select at least one quotation to print.');
        }

        $data = $request->validate([
            'quotation_ids' => ['required', 'array', 'min:1'],
            'quotation_ids.*' => ['integer', 'distinct'],
        ]);

        $quotationIds = collect($data['quotation_ids'])->map(fn ($id) => (int) $id)->values();
        $quotations = Quotation::with(['customer', 'items'])
            ->where('tenant_id', $tenant->id)
            ->whereIn('id', $quotationIds->all())
            ->orderBy('inquiry_date')
            ->orderBy('id')
            ->get();

        if ($quotations->count() !== $quotationIds->count()) {
            return back()->with('error', 'Some selected quotations are invalid.');
        }

        if ($quotations->pluck('customer_id')->unique()->count() !== 1) {
            return back()->with('error', 'Please select quotations of the same customer only.');
        }

        $customer = $quotations->first()?->customer;
        $companyProfile = $this->companyProfileData($tenant->id);
        $payload = [
            'quotations' => $quotations,
            'customer' => $customer,
            'companyProfile' => $companyProfile,
            'printDate' => now(),
        ];

        $pdf = Pdf::loadView('print.quotation', $payload)->setPaper('a4', 'portrait');

        $filename = 'quotation-' . ($customer?->company_name ? str($customer->company_name)->slug() : 'batch') . '-' . now()->format('Ymd-His');

        return $pdf->download($filename . '.pdf');
    }

    public function previewOrderCalculation(Request $request)
    {
        $payload = $request->validate([
            'total_pages' => ['required', 'integer', 'min:1'],
            'page_size' => ['required', 'string'],
            'custom_width' => ['nullable', 'numeric', 'min:0.1'],
            'custom_height' => ['nullable', 'numeric', 'min:0.1'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'standard_sheet_size' => ['required', 'string', 'max:100'],
            'colors' => ['required', 'integer', 'min:1', 'max:4'],
            'printing_style' => ['required', 'in:work_and_turn,work_and_back'],
        ]);

        return response()->json($this->printCalculationService->calculate($payload));
    }

    private function tenant(): Tenant
    {
        return Tenant::firstOrFail();
    }

    private function redirectPage(string $module): string
    {
        if (in_array($module, ['users', 'roles', 'paper-types', 'ink-types', 'standard-sheets', 'units'], true)) {
            return 'portal.page';
        }

        return $module === 'dashboard' ? 'portal.home' : 'portal.page';
    }

    private function redirectParams(string $module): array
    {
        if (in_array($module, ['users', 'roles'], true)) {
            return ['page' => 'users-roles'];
        }
        if (in_array($module, ['paper-types', 'ink-types', 'standard-sheets', 'units'], true)) {
            return ['page' => 'settings'];
        }

        return $module === 'dashboard' ? [] : ['page' => $module];
    }

    private function options(): array
    {
        $tenant = $this->tenant();

        return [
            'customers' => Customer::where('tenant_id', $tenant->id)->pluck('company_name', 'id')->all(),
            'suppliers' => Supplier::where('tenant_id', $tenant->id)->pluck('company_name', 'id')->all(),
            'products' => Product::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'product_categories' => ProductCategory::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'raw_materials' => RawMaterial::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'raw_material_categories' => RawMaterialCategory::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'warehouses' => Warehouse::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'quotations' => Quotation::where('tenant_id', $tenant->id)->pluck('quote_number', 'id')->all(),
            'orders' => Order::where('tenant_id', $tenant->id)->pluck('order_number', 'id')->all(),
            'users' => User::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'roles' => \App\Models\Role::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'paper_types' => PaperType::where(fn ($q) => $q->where('tenant_id', $tenant->id)->orWhereNull('tenant_id'))->pluck('name', 'id')->all(),
            'ink_types' => InkType::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'standard_sheets' => StandardSheet::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
            'units' => Unit::where('tenant_id', $tenant->id)->pluck('name', 'id')->all(),
        ];
    }

    private function printingOrderRules(): array
    {
        return [
            'job_number' => ['required', 'string', 'max:50'],
            'job_title' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'gsm' => ['required', 'integer', 'min:40', 'max:1000'],
            'paper_type_id' => ['required', 'exists:paper_types,id'],
            'ink_type' => ['required', 'string', 'max:50'],
            'pantone_codes' => ['nullable', 'string', 'max:255'],
            'finish_type' => ['required', 'string', 'max:50'],
            'total_pages' => ['required', 'integer', 'min:1'],
            'page_size' => ['required', 'string', 'max:50'],
            'custom_width' => ['nullable', 'numeric', 'min:0.1'],
            'custom_height' => ['nullable', 'numeric', 'min:0.1'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'standard_sheet_size' => ['required', 'string', 'max:100'],
            'colors' => ['required', 'integer', 'min:1', 'max:4'],
            'printing_style' => ['required', 'in:work_and_turn,work_and_back'],
            'estimated_material_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_other_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_unit_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,confirmed,in_production,quality_check,delivered'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function enforceAdvancePaymentAndStock(JobOrder $jobOrder): void
    {
        $requiredAdvance = (float) $jobOrder->estimated_total_cost * ((float) config('printing.advance_payment_percent', 50) / 100);
        $advancePaid = (float) JobPayment::query()
            ->where('job_order_id', $jobOrder->id)
            ->where('payment_stage', 'advance')
            ->sum('amount');

        if ($advancePaid + 0.0001 < $requiredAdvance) {
            abort(422, 'Cannot move to production. Minimum 50% advance payment is required.');
        }

        $calc = JobCalculation::query()->where('job_order_id', $jobOrder->id)->latest('computed_at')->first();
        if (! $calc) {
            abort(422, 'No print calculation found for this job.');
        }

        $stock = PaperStock::query()
            ->where('tenant_id', $jobOrder->tenant_id)
            ->where('paper_type_id', $jobOrder->paper_type_id)
            ->where('gsm', $jobOrder->gsm)
            ->where('sheet_size', $jobOrder->standard_sheet_size)
            ->first();

        if (! $stock) {
            abort(422, 'No paper stock record found for this job.');
        }

        $availableSheets = ((int) $stock->stock_reams * 500) + ((int) $stock->stock_quires * 25) + (int) $stock->stock_sheets;
        $requiredSheets = (int) $calc->total_sheets;

        if ($availableSheets < $requiredSheets) {
            $shortfall = $requiredSheets - $availableSheets;

            JobPurchaseOrder::create([
                'tenant_id' => $jobOrder->tenant_id,
                'supplier_id' => Supplier::query()->where('tenant_id', $jobOrder->tenant_id)->value('id'),
                'job_order_id' => $jobOrder->id,
                'po_number' => 'AUTO-PO-' . now()->format('YmdHis'),
                'order_date' => now()->toDateString(),
                'expected_date' => now()->addDays(2)->toDateString(),
                'status' => 'draft',
                'is_auto_suggested' => true,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'notes' => 'Auto-generated for shortfall: ' . $shortfall . ' sheets',
            ]);

            abort(422, 'Insufficient paper stock. Auto PO suggestion created for shortfall.');
        }

        $remainingSheets = $availableSheets - $requiredSheets;
        $stock->update([
            'stock_reams' => intdiv($remainingSheets, 500),
            'stock_quires' => intdiv($remainingSheets % 500, 25),
            'stock_sheets' => $remainingSheets % 25,
        ]);
    }

    private function printPayload(string $module, int $id, int $tenantId): ?array
    {
        $payload = match ($module) {
            'quotations' => $this->quotationPrintPayload($id, $tenantId),
            'orders' => $this->orderPrintPayload($id, $tenantId),
            'invoices' => $this->invoicePrintPayload($id, $tenantId),
            'purchases' => $this->purchasePrintPayload($id, $tenantId),
            'deliveries' => $this->deliveryPrintPayload($id, $tenantId),
            'customers' => $this->simplePrintPayload('print.customer', Customer::where('tenant_id', $tenantId)->findOrFail($id), 'customer-' . $id),
            'suppliers' => $this->simplePrintPayload('print.supplier', Supplier::where('tenant_id', $tenantId)->findOrFail($id), 'supplier-' . $id),
            'expenses' => $this->simplePrintPayload('print.expense', Expense::where('tenant_id', $tenantId)->findOrFail($id), 'expense-' . $id),
            default => null,
        };

        if ($payload) {
            return $payload;
        }

        $config = $this->config($module);
        if (! $config) {
            return null;
        }

        $record = $config['model']::query()->findOrFail($id);

        return [
            'view' => 'print.generic',
            'data' => ['record' => $record, 'module' => $module],
            'filename' => $module . '-' . $id,
        ];
    }

    private function quotationPrintPayload(int $id, int $tenantId): array
    {
        $quotation = Quotation::with(['customer', 'items'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);
        $companyProfile = $this->companyProfileData($tenantId);
        $quotations = collect([$quotation]);
        $customer = $quotation->customer;
        $printDate = now();

        return [
            'view' => 'print.quotation',
            'data' => compact('quotations', 'customer', 'companyProfile', 'printDate'),
            'filename' => 'quotation-' . $quotation->quote_number,
        ];
    }

    private function orderPrintPayload(int $id, int $tenantId): array
    {
        $order = JobOrder::with(['customer', 'paperType', 'calculations', 'payments', 'plates'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        return [
            'view' => 'print.order',
            'data' => compact('order'),
            'filename' => 'job-order-' . $order->job_number,
        ];
    }

    private function invoicePrintPayload(int $id, int $tenantId): array
    {
        $invoice = Invoice::with(['customer', 'order'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        return [
            'view' => 'print.invoice',
            'data' => compact('invoice'),
            'filename' => 'invoice-' . $invoice->invoice_number,
        ];
    }

    private function purchasePrintPayload(int $id, int $tenantId): array
    {
        $purchase = PurchaseOrder::with(['supplier', 'warehouse', 'items', 'jobOrder'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        return [
            'view' => 'print.purchase',
            'data' => compact('purchase'),
            'filename' => 'purchase-' . $purchase->po_number,
        ];
    }

    private function deliveryPrintPayload(int $id, int $tenantId): array
    {
        $delivery = Delivery::with(['order.customer'])
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        return [
            'view' => 'print.delivery',
            'data' => compact('delivery'),
            'filename' => 'delivery-' . $delivery->delivery_number,
        ];
    }

    private function simplePrintPayload(string $view, mixed $record, string $filename): array
    {
        return [
            'view' => $view,
            'data' => ['record' => $record],
            'filename' => $filename,
        ];
    }

    private function companyProfileData(int $tenantId): array
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $companyProfile = (array) optional(
            Setting::query()
                ->where('tenant_id', $tenantId)
                ->where('key', 'company_profile')
                ->first()
        )->value_json;

        return [
            'company_name' => $companyProfile['company_name'] ?? $tenant->name,
            'tagline' => $companyProfile['tagline'] ?? null,
            'address' => $companyProfile['address'] ?? $tenant->address,
            'phone' => $companyProfile['phone'] ?? $tenant->phone,
            'email' => $companyProfile['email'] ?? $tenant->email,
            'website' => $companyProfile['website'] ?? null,
            'vat_no' => $companyProfile['vat_no'] ?? null,
            'bin_no' => $companyProfile['bin_no'] ?? null,
            'trade_license_no' => $companyProfile['trade_license_no'] ?? null,
            'logo_url' => $companyProfile['logo_url'] ?? $tenant->logo,
            'signature_name' => $companyProfile['signature_name'] ?? null,
            'signature_title' => $companyProfile['signature_title'] ?? null,
            'quotation_footer_note' => $companyProfile['quotation_footer_note'] ?? 'Note: This Quotation Is Excluding Vat & Tax.',
        ];
    }

    private function quotationRules(): array
    {
        return [
            'quote_number' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'inquiry_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,sent,approved'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'profit_percentage' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function syncQuotationItems(Quotation $quotation, array $items, bool $replace = false): void
    {
        if ($replace) {
            $quotation->items()->delete();
        }

        foreach ($items as $item) {
            QuotationItem::create([
                'tenant_id' => $quotation->tenant_id,
                'quotation_id' => $quotation->id,
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'total_price' => ((float) $item['quantity']) * ((float) $item['unit_price']),
                'specification_json' => null,
            ]);
        }
    }

    private function config(string $module): ?array
    {
        return [
            'customers' => [
                'title' => 'Add Customer',
                'model' => Customer::class,
                'success' => 'Customer created successfully.',
                'fields' => [
                    ['name' => 'customer_code', 'label' => 'Customer Code', 'type' => 'text'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text'],
                    ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'city', 'label' => 'City', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'vip' => 'VIP', 'lead' => 'Lead']],
                ],
                'rules' => [
                    'customer_code' => ['required', 'string', 'max:255'],
                    'company_name' => ['required', 'string', 'max:255'],
                    'contact_person' => ['nullable', 'string', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:255'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Company', 'Contact', 'Phone', 'Email', 'City', 'Status']], Customer::where('tenant_id', $tenant->id)->get(['customer_code', 'company_name', 'contact_person', 'phone', 'email', 'city', 'status'])->map(fn ($c) => [$c->customer_code, $c->company_name, $c->contact_person, $c->phone, $c->email, $c->city, $c->status])->all()),
            ],
            'suppliers' => [
                'title' => 'Add Supplier',
                'model' => Supplier::class,
                'success' => 'Supplier created successfully.',
                'fields' => [
                    ['name' => 'supplier_code', 'label' => 'Supplier Code', 'type' => 'text'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text'],
                    ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'preferred' => 'Preferred', 'on_hold' => 'On Hold']],
                ],
                'rules' => [
                    'supplier_code' => ['required', 'string', 'max:255'],
                    'company_name' => ['required', 'string', 'max:255'],
                    'contact_person' => ['nullable', 'string', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:255'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Company', 'Contact', 'Phone', 'Email', 'Status']], Supplier::where('tenant_id', $tenant->id)->get(['supplier_code', 'company_name', 'contact_person', 'phone', 'email', 'status'])->map(fn ($s) => [$s->supplier_code, $s->company_name, $s->contact_person, $s->phone, $s->email, $s->status])->all()),
            ],
            'products' => [
                'title' => 'Add Product',
                'model' => Product::class,
                'success' => 'Product created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Product Name', 'type' => 'text'],
                    ['name' => 'sku', 'label' => 'SKU', 'type' => 'text'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'source' => 'product_categories'],
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['service' => 'Service', 'item' => 'Item']],
                    ['name' => 'base_price', 'label' => 'Base Price', 'type' => 'number'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'draft' => 'Draft']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'sku' => ['nullable', 'string', 'max:255'],
                    'category_id' => ['nullable', 'integer'],
                    'type' => ['required', 'string'],
                    'base_price' => ['required', 'numeric'],
                    'unit' => ['required', 'string', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['SKU', 'Name', 'Type', 'Base Price', 'Unit', 'Status']], Product::where('tenant_id', $tenant->id)->get(['sku', 'name', 'type', 'base_price', 'unit', 'status'])->map(fn ($p) => [$p->sku, $p->name, $p->type, $p->base_price, $p->unit, $p->status])->all()),
            ],
            'raw-materials' => [
                'title' => 'Add Raw Material',
                'model' => RawMaterial::class,
                'success' => 'Raw material created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Material Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'source' => 'raw_material_categories'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                    ['name' => 'current_stock', 'label' => 'Current Stock', 'type' => 'number'],
                    ['name' => 'minimum_stock', 'label' => 'Minimum Stock', 'type' => 'number'],
                    ['name' => 'average_cost', 'label' => 'Average Cost', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:255'],
                    'category_id' => ['nullable', 'integer'],
                    'unit' => ['required', 'string', 'max:255'],
                    'current_stock' => ['required', 'numeric'],
                    'minimum_stock' => ['required', 'numeric'],
                    'average_cost' => ['required', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Name', 'Unit', 'Current Stock', 'Minimum Stock', 'Average Cost', 'Status']], RawMaterial::where('tenant_id', $tenant->id)->get(['code', 'name', 'unit', 'current_stock', 'minimum_stock', 'average_cost', 'status'])->map(fn ($m) => [$m->code, $m->name, $m->unit, $m->current_stock, $m->minimum_stock, $m->average_cost, $m->status])->all()),
            ],
            'warehouses' => [
                'title' => 'Add Warehouse',
                'model' => Warehouse::class,
                'success' => 'Warehouse created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Warehouse Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'manager_name', 'label' => 'Manager Name', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:255'],
                    'manager_name' => ['nullable', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Code', 'Name', 'Manager', 'Address', 'Status']], Warehouse::where('tenant_id', $tenant->id)->get(['code', 'name', 'manager_name', 'address', 'status'])->map(fn ($w) => [$w->code, $w->name, $w->manager_name, $w->address, $w->status])->all()),
            ],
            'paper-types' => [
                'title' => 'Add Paper Type',
                'model' => PaperType::class,
                'success' => 'Paper type created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Paper Type Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'text'],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['nullable', 'string', 'max:100'],
                    'status' => ['required', 'string', 'max:50'],
                    'notes' => ['nullable', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Name', 'Code', 'Status', 'Notes']], PaperType::where(fn ($q) => $q->where('tenant_id', $tenant->id)->orWhereNull('tenant_id'))->get(['name', 'code', 'status', 'notes'])->map(fn ($p) => [$p->name, $p->code, $p->status, $p->notes])->all()),
            ],
            'ink-types' => [
                'title' => 'Add Ink Type',
                'model' => InkType::class,
                'success' => 'Ink type created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Ink Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'pantone_code', 'label' => 'Pantone Code', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'text'],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['nullable', 'string', 'max:100'],
                    'pantone_code' => ['nullable', 'string', 'max:100'],
                    'status' => ['required', 'string', 'max:50'],
                    'notes' => ['nullable', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Name', 'Code', 'Pantone', 'Status', 'Notes']], InkType::where('tenant_id', $tenant->id)->get(['name', 'code', 'pantone_code', 'status', 'notes'])->map(fn ($i) => [$i->name, $i->code, $i->pantone_code, $i->status, $i->notes])->all()),
            ],
            'standard-sheets' => [
                'title' => 'Add Standard Sheet Unit',
                'model' => StandardSheet::class,
                'success' => 'Standard sheet saved successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Sheet Name', 'type' => 'text'],
                    ['name' => 'code', 'label' => 'Code', 'type' => 'text'],
                    ['name' => 'width_in', 'label' => 'Width (inch)', 'type' => 'number'],
                    ['name' => 'height_in', 'label' => 'Height (inch)', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:100'],
                    'width_in' => ['required', 'numeric', 'min:0.1'],
                    'height_in' => ['required', 'numeric', 'min:0.1'],
                    'status' => ['required', 'string', 'max:50'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Name', 'Code', 'Width', 'Height', 'Status']], StandardSheet::where('tenant_id', $tenant->id)->get(['name', 'code', 'width_in', 'height_in', 'status'])->map(fn ($s) => [$s->name, $s->code, $s->width_in, $s->height_in, $s->status])->all()),
            ],
            'units' => [
                'title' => 'Add Unit',
                'model' => Unit::class,
                'success' => 'Unit created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Unit Name', 'type' => 'text'],
                    ['name' => 'symbol', 'label' => 'Symbol', 'type' => 'text'],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'text'],
                    ['name' => 'base_quantity', 'label' => 'Base Quantity', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'symbol' => ['nullable', 'string', 'max:30'],
                    'category' => ['nullable', 'string', 'max:100'],
                    'base_quantity' => ['nullable', 'numeric', 'min:0.0001'],
                    'status' => ['required', 'string', 'max:50'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id, 'base_quantity' => $data['base_quantity'] ?? 1]),
                'export' => fn ($tenant) => array_merge([['Name', 'Symbol', 'Category', 'Base Quantity', 'Status']], Unit::where('tenant_id', $tenant->id)->get(['name', 'symbol', 'category', 'base_quantity', 'status'])->map(fn ($u) => [$u->name, $u->symbol, $u->category, $u->base_quantity, $u->status])->all()),
            ],
            'quotations' => [
                'title' => 'Create Quotation',
                'model' => Quotation::class,
                'success' => 'Quotation created successfully.',
                'fields' => [
                    ['name' => 'quote_number', 'label' => 'Quote Number', 'type' => 'text'],
                    ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'source' => 'customers'],
                    ['name' => 'inquiry_date', 'label' => 'Inquiry Date', 'type' => 'date'],
                    ['name' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'sent' => 'Sent', 'approved' => 'Approved']],
                ],
                'rules' => [
                    'quote_number' => ['required', 'string', 'max:255'],
                    'customer_id' => ['required', 'integer'],
                    'inquiry_date' => ['nullable', 'date'],
                    'valid_until' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'total' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'created_by' => $user?->id,
                    'approved_at' => ($data['status'] ?? null) === 'approved' ? now() : null,
                ]),
                'export' => fn ($tenant) => array_merge([['Quote Number', 'Customer ID', 'Inquiry Date', 'Valid Until', 'Total', 'Status']], Quotation::where('tenant_id', $tenant->id)->get(['quote_number', 'customer_id', 'inquiry_date', 'valid_until', 'total', 'status'])->map(fn ($q) => [$q->quote_number, $q->customer_id, $q->inquiry_date, $q->valid_until, $q->total, $q->status])->all()),
            ],
            'orders' => [
                'title' => 'Create Order',
                'model' => Order::class,
                'success' => 'Order created successfully.',
                'fields' => [
                    ['name' => 'order_number', 'label' => 'Order Number', 'type' => 'text'],
                    ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'source' => 'customers'],
                    ['name' => 'quotation_id', 'label' => 'Quotation', 'type' => 'select', 'source' => 'quotations', 'nullable' => true],
                    ['name' => 'job_title', 'label' => 'Job Title', 'type' => 'text'],
                    ['name' => 'order_date', 'label' => 'Order Date', 'type' => 'date'],
                    ['name' => 'expected_delivery_date', 'label' => 'Expected Delivery Date', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approval' => 'Approval', 'printing' => 'Printing', 'finishing' => 'Finishing']],
                ],
                'rules' => [
                    'order_number' => ['required', 'string', 'max:255'],
                    'customer_id' => ['required', 'integer'],
                    'quotation_id' => ['nullable', 'integer'],
                    'job_title' => ['required', 'string', 'max:255'],
                    'order_date' => ['nullable', 'date'],
                    'expected_delivery_date' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'total' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'paid_amount' => 0,
                    'due_amount' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'created_by' => $user?->id,
                    'assigned_manager_id' => $user?->id,
                    'priority' => 'normal',
                ]),
                'export' => fn ($tenant) => array_merge([['Order Number', 'Customer ID', 'Job Title', 'Order Date', 'Expected Delivery', 'Total', 'Status']], Order::where('tenant_id', $tenant->id)->get(['order_number', 'customer_id', 'job_title', 'order_date', 'expected_delivery_date', 'total', 'status'])->map(fn ($o) => [$o->order_number, $o->customer_id, $o->job_title, $o->order_date, $o->expected_delivery_date, $o->total, $o->status])->all()),
            ],
            'purchases' => [
                'title' => 'Create Purchase Order',
                'model' => PurchaseOrder::class,
                'success' => 'Purchase order created successfully.',
                'fields' => [
                    ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'text'],
                    ['name' => 'supplier_id', 'label' => 'Supplier', 'type' => 'select', 'source' => 'suppliers'],
                    ['name' => 'warehouse_id', 'label' => 'Warehouse', 'type' => 'select', 'source' => 'warehouses'],
                    ['name' => 'order_date', 'label' => 'Order Date', 'type' => 'date'],
                    ['name' => 'expected_date', 'label' => 'Expected Date', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'ordered' => 'Ordered', 'partial_received' => 'Partial Received']],
                ],
                'rules' => [
                    'po_number' => ['required', 'string', 'max:255'],
                    'supplier_id' => ['required', 'integer'],
                    'warehouse_id' => ['nullable', 'integer'],
                    'order_date' => ['nullable', 'date'],
                    'expected_date' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'total' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'paid_amount' => 0,
                    'due_amount' => ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0),
                    'created_by' => $user?->id,
                ]),
                'export' => fn ($tenant) => array_merge([['PO Number', 'Supplier ID', 'Warehouse ID', 'Order Date', 'Expected Date', 'Total', 'Status']], PurchaseOrder::where('tenant_id', $tenant->id)->get(['po_number', 'supplier_id', 'warehouse_id', 'order_date', 'expected_date', 'total', 'status'])->map(fn ($p) => [$p->po_number, $p->supplier_id, $p->warehouse_id, $p->order_date, $p->expected_date, $p->total, $p->status])->all()),
            ],
            'invoices' => [
                'title' => 'Create Invoice',
                'model' => Invoice::class,
                'success' => 'Invoice created successfully.',
                'fields' => [
                    ['name' => 'invoice_number', 'label' => 'Invoice Number', 'type' => 'text'],
                    ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'source' => 'customers'],
                    ['name' => 'order_id', 'label' => 'Order', 'type' => 'select', 'source' => 'orders', 'nullable' => true],
                    ['name' => 'invoice_date', 'label' => 'Invoice Date', 'type' => 'date'],
                    ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'date'],
                    ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number'],
                    ['name' => 'discount', 'label' => 'Discount', 'type' => 'number'],
                    ['name' => 'tax', 'label' => 'Tax', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'due' => 'Due', 'paid' => 'Paid']],
                ],
                'rules' => [
                    'invoice_number' => ['required', 'string', 'max:255'],
                    'customer_id' => ['required', 'integer'],
                    'order_id' => ['nullable', 'integer'],
                    'invoice_date' => ['nullable', 'date'],
                    'due_date' => ['nullable', 'date'],
                    'subtotal' => ['required', 'numeric'],
                    'discount' => ['nullable', 'numeric'],
                    'tax' => ['nullable', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => function ($data, $tenant, $user) {
                    $total = ($data['subtotal'] ?? 0) - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);

                    return array_merge($data, [
                        'tenant_id' => $tenant->id,
                        'total' => $total,
                        'paid_amount' => ($data['status'] ?? null) === 'paid' ? $total : 0,
                        'due_amount' => ($data['status'] ?? null) === 'paid' ? 0 : $total,
                        'created_by' => $user?->id,
                    ]);
                },
                'export' => fn ($tenant) => array_merge([['Invoice Number', 'Customer ID', 'Invoice Date', 'Due Date', 'Total', 'Status']], Invoice::where('tenant_id', $tenant->id)->get(['invoice_number', 'customer_id', 'invoice_date', 'due_date', 'total', 'status'])->map(fn ($i) => [$i->invoice_number, $i->customer_id, $i->invoice_date, $i->due_date, $i->total, $i->status])->all()),
            ],
            'expenses' => [
                'title' => 'Add Expense',
                'model' => Expense::class,
                'success' => 'Expense created successfully.',
                'fields' => [
                    ['name' => 'expense_date', 'label' => 'Expense Date', 'type' => 'date'],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => ['Utility' => 'Utility', 'Transport' => 'Transport', 'Maintenance' => 'Maintenance', 'Office' => 'Office']],
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['name' => 'reference_no', 'label' => 'Reference No', 'type' => 'text'],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number'],
                ],
                'rules' => [
                    'expense_date' => ['nullable', 'date'],
                    'category' => ['required', 'string', 'max:255'],
                    'title' => ['required', 'string', 'max:255'],
                    'reference_no' => ['nullable', 'string', 'max:255'],
                    'amount' => ['required', 'numeric'],
                ],
                'payload' => fn ($data, $tenant, $user) => array_merge($data, ['tenant_id' => $tenant->id, 'created_by' => $user?->id]),
                'export' => fn ($tenant) => array_merge([['Date', 'Category', 'Title', 'Reference No', 'Amount']], Expense::where('tenant_id', $tenant->id)->get(['expense_date', 'category', 'title', 'reference_no', 'amount'])->map(fn ($e) => [$e->expense_date, $e->category, $e->title, $e->reference_no, $e->amount])->all()),
            ],
            'deliveries' => [
                'title' => 'Create Delivery',
                'model' => Delivery::class,
                'success' => 'Delivery created successfully.',
                'fields' => [
                    ['name' => 'delivery_number', 'label' => 'Delivery Number', 'type' => 'text'],
                    ['name' => 'order_id', 'label' => 'Order', 'type' => 'select', 'source' => 'orders'],
                    ['name' => 'delivery_date', 'label' => 'Delivery Date', 'type' => 'date'],
                    ['name' => 'assigned_to', 'label' => 'Assigned To', 'type' => 'select', 'source' => 'users'],
                    ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text'],
                    ['name' => 'transport_cost', 'label' => 'Transport Cost', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'assigned' => 'Assigned', 'out_for_delivery' => 'Out For Delivery', 'delivered' => 'Delivered']],
                ],
                'rules' => [
                    'delivery_number' => ['required', 'string', 'max:255'],
                    'order_id' => ['required', 'integer'],
                    'delivery_date' => ['nullable', 'date'],
                    'assigned_to' => ['nullable', 'integer'],
                    'vehicle_no' => ['nullable', 'string', 'max:255'],
                    'transport_cost' => ['required', 'numeric'],
                    'status' => ['required', 'string'],
                ],
                'payload' => fn ($data, $tenant) => array_merge($data, ['tenant_id' => $tenant->id]),
                'export' => fn ($tenant) => array_merge([['Delivery Number', 'Order ID', 'Delivery Date', 'Assigned To', 'Vehicle No', 'Transport Cost', 'Status']], Delivery::where('tenant_id', $tenant->id)->get(['delivery_number', 'order_id', 'delivery_date', 'assigned_to', 'vehicle_no', 'transport_cost', 'status'])->map(fn ($d) => [$d->delivery_number, $d->order_id, $d->delivery_date, $d->assigned_to, $d->vehicle_no, $d->transport_cost, $d->status])->all()),
            ],
            'users' => [
                'title' => 'Add User',
                'model' => User::class,
                'success' => 'User created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                    ['name' => 'role_id', 'label' => 'Role', 'type' => 'select', 'source' => 'roles'],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:255'],
                    'password' => ['nullable', 'string', 'min:4'],
                    'status' => ['required', 'string'],
                    'role_id' => ['required', 'integer'],
                ],
                'payload' => function ($data, $tenant) {
                    $payload = [
                        'tenant_id' => $tenant->id,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'status' => $data['status'],
                        'email_verified_at' => now(),
                    ];

                    if (! empty($data['password'])) {
                        $payload['password'] = $data['password'];
                    }

                    return $payload;
                },
                'after_store' => fn (User $user, array $data) => $user->roles()->sync([$data['role_id']]),
                'after_update' => fn (User $user, array $data) => $user->roles()->sync([$data['role_id']]),
                'export' => fn ($tenant) => array_merge([['Name', 'Email', 'Phone', 'Status']], User::where('tenant_id', $tenant->id)->get(['name', 'email', 'phone', 'status'])->map(fn ($u) => [$u->name, $u->email, $u->phone, $u->status])->all()),
            ],
            'roles' => [
                'title' => 'Add Role',
                'model' => \App\Models\Role::class,
                'success' => 'Role created successfully.',
                'fields' => [
                    ['name' => 'name', 'label' => 'Role Name', 'type' => 'text'],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                ],
                'payload' => fn ($data, $tenant) => [
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'guard_name' => 'web',
                ],
                'export' => fn ($tenant) => array_merge([['Role Name']], \App\Models\Role::where('tenant_id', $tenant->id)->get(['name'])->map(fn ($r) => [$r->name])->all()),
            ],
        ][$module] ?? null;
    }
}
