<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryChallanRequest;
use App\Http\Requests\UpdateDeliveryChallanRequest;
use App\Models\DeliveryChallan;
use App\Models\JobOrder;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryChallanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(DeliveryChallan::class, 'delivery_challan');
    }

    public function index(): View
    {
        $challans = DeliveryChallan::with('jobOrder')->latest()->paginate(20);

        return view('delivery-challans.index', compact('challans'));
    }

    public function create(): View
    {
        return view('delivery-challans.create', [
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function store(StoreDeliveryChallanRequest $request): RedirectResponse
    {
        DeliveryChallan::create(array_merge($request->validated(), [
            'tenant_id' => Tenant::query()->value('id'),
            'created_by' => $request->user()?->id,
        ]));

        return redirect()->route('delivery-challans.index')->with('success', 'Delivery challan created successfully.');
    }

    public function show(DeliveryChallan $deliveryChallan): View
    {
        return view('delivery-challans.show', compact('deliveryChallan'));
    }

    public function edit(DeliveryChallan $deliveryChallan): View
    {
        return view('delivery-challans.edit', [
            'deliveryChallan' => $deliveryChallan,
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function update(UpdateDeliveryChallanRequest $request, DeliveryChallan $deliveryChallan): RedirectResponse
    {
        $deliveryChallan->update($request->validated());

        return redirect()->route('delivery-challans.index')->with('success', 'Delivery challan updated successfully.');
    }

    public function destroy(DeliveryChallan $deliveryChallan): RedirectResponse
    {
        $deliveryChallan->delete();

        return redirect()->route('delivery-challans.index')->with('success', 'Delivery challan deleted successfully.');
    }

    public function pdf(DeliveryChallan $deliveryChallan)
    {
        $pdf = Pdf::loadView('delivery-challans.pdf', compact('deliveryChallan'));

        return $pdf->download($deliveryChallan->challan_number . '.pdf');
    }
}
