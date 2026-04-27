<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCtpRequest;
use App\Http\Requests\UpdateCtpRequest;
use App\Models\Ctp;
use App\Models\JobOrder;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CtpController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Ctp::class, 'ctp');
    }

    public function index(): View
    {
        $ctps = Ctp::with('jobOrder')->latest()->paginate(20);

        return view('ctps.index', compact('ctps'));
    }

    public function create(): View
    {
        return view('ctps.create', [
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function store(StoreCtpRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data): void {
            $totalPlateCost = (float) $data['quantity'] * (float) $data['cost_per_plate'];
            Ctp::create(array_merge($data, [
                'tenant_id' => Tenant::query()->value('id'),
                'total_plate_cost' => $totalPlateCost,
                'issued_by' => $request->user()?->id,
            ]));

            $jobOrder = JobOrder::query()->findOrFail($data['job_order_id']);
            $jobOrder->update([
                'estimated_plate_cost' => $jobOrder->plates()->sum('total_plate_cost'),
                'estimated_total_cost' => $jobOrder->estimated_material_cost + $jobOrder->estimated_other_cost + $jobOrder->plates()->sum('total_plate_cost'),
            ]);
        });

        return redirect()->route('ctps.index')->with('success', 'CTP issue recorded and job cost updated.');
    }

    public function show(Ctp $ctp): View
    {
        return view('ctps.show', compact('ctp'));
    }

    public function edit(Ctp $ctp): View
    {
        return view('ctps.edit', [
            'ctp' => $ctp,
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function update(UpdateCtpRequest $request, Ctp $ctp): RedirectResponse
    {
        $data = $request->validated();
        $ctp->update(array_merge($data, [
            'total_plate_cost' => (float) $data['quantity'] * (float) $data['cost_per_plate'],
        ]));

        return redirect()->route('ctps.index')->with('success', 'CTP updated successfully.');
    }

    public function destroy(Ctp $ctp): RedirectResponse
    {
        $ctp->delete();

        return redirect()->route('ctps.index')->with('success', 'CTP deleted successfully.');
    }
}
