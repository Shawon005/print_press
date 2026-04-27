<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\JobOrder;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse'])->latest()->paginate(20);

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function create(): View
    {
        return view('purchase-orders.create', [
            'suppliers' => Supplier::orderBy('company_name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $total = $data['subtotal'] - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);

        PurchaseOrder::create(array_merge($data, [
            'tenant_id' => Tenant::query()->value('id'),
            'total' => $total,
            'paid_amount' => 0,
            'due_amount' => $total,
            'created_by' => $request->user()?->id,
        ]));

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.edit', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => Supplier::orderBy('company_name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'jobOrders' => JobOrder::orderBy('job_number')->get(),
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->validated();
        $total = $data['subtotal'] - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);
        $purchaseOrder->update(array_merge($data, [
            'total' => $total,
            'due_amount' => $total - $purchaseOrder->paid_amount,
        ]));

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }
}
