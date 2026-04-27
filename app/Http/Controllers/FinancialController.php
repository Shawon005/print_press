<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobPaymentRequest;
use App\Http\Requests\UpdateJobPaymentRequest;
use App\Models\JobOrder;
use App\Models\JobPayment;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(JobPayment::class, 'financial');
    }

    public function index(): View
    {
        $jobOrders = JobOrder::with('customer')
            ->withSum('payments as paid_amount', 'amount')
            ->orderByDesc('id')
            ->get()
            ->map(function (JobOrder $jobOrder) {
                $paid = (float) ($jobOrder->paid_amount ?? 0);
                $jobOrder->balance_due = max(0, (float) $jobOrder->estimated_total_price - $paid);
                $jobOrder->advance_required = (float) $jobOrder->estimated_total_cost * ((float) config('printing.advance_payment_percent', 50) / 100);
                $jobOrder->advance_paid = (float) $jobOrder->payments()->where('payment_stage', 'advance')->sum('amount');
                $jobOrder->advance_met = $jobOrder->advance_paid >= $jobOrder->advance_required;

                return $jobOrder;
            });

        return view('financials.index', compact('jobOrders'));
    }

    public function create(): View
    {
        return view('financials.create', [
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function store(StoreJobPaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $jobOrder = JobOrder::query()->findOrFail($data['job_order_id']);

        if ($data['payment_stage'] === 'advance') {
            $minimumAdvancePercent = (float) config('printing.advance_payment_percent', 50);
            $minimumAdvanceAmount = (float) $jobOrder->estimated_total_cost * ($minimumAdvancePercent / 100);
            $existingAdvance = (float) $jobOrder->payments()->where('payment_stage', 'advance')->sum('amount');
            $newAdvanceTotal = $existingAdvance + (float) $data['amount'];

            if ($newAdvanceTotal + 0.0001 < $minimumAdvanceAmount) {
                return back()->withErrors([
                    'amount' => sprintf('Advance does not meet %.0f%% requirement. Required minimum: %.2f', $minimumAdvancePercent, $minimumAdvanceAmount),
                ])->withInput();
            }
        }

        JobPayment::create(array_merge($data, [
            'tenant_id' => Tenant::query()->value('id'),
            'recorded_by' => $request->user()?->id,
        ]));

        return redirect()->route('financials.index')->with('success', 'Payment recorded successfully.');
    }

    public function show(JobPayment $financial): View
    {
        return view('financials.show', ['payment' => $financial]);
    }

    public function edit(JobPayment $financial): View
    {
        return view('financials.edit', [
            'payment' => $financial,
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function update(UpdateJobPaymentRequest $request, JobPayment $financial): RedirectResponse
    {
        $financial->update($request->validated());

        return redirect()->route('financials.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(JobPayment $financial): RedirectResponse
    {
        $financial->delete();

        return redirect()->route('financials.index')->with('success', 'Payment deleted successfully.');
    }

    public function enforceProductionGate(Request $request, JobOrder $jobOrder): RedirectResponse
    {
        $requiredAdvance = (float) $jobOrder->estimated_total_cost * ((float) config('printing.advance_payment_percent', 50) / 100);
        $advancePaid = (float) $jobOrder->payments()->where('payment_stage', 'advance')->sum('amount');

        if ($advancePaid + 0.0001 < $requiredAdvance) {
            return back()->withErrors([
                'advance' => 'Cannot proceed to production until minimum 50% advance is recorded.',
            ]);
        }

        $jobOrder->update(['status' => 'in_production']);

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Production gate passed and status updated.');
    }
}
