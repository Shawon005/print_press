<?php

namespace App\Http\Controllers;

use App\Jobs\SendLowStockAlertJob;
use App\Http\Requests\StorePaperStockRequest;
use App\Http\Requests\UpdatePaperStockRequest;
use App\Models\JobOrder;
use App\Models\PaperStock;
use App\Models\PaperStockMovement;
use App\Models\PaperType;
use App\Models\PurchaseOrder;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PaperStock::class, 'inventory');
    }

    public function index(): View
    {
        $stocks = PaperStock::with('paperType')->get()->map(function (PaperStock $stock) {
            $stock->is_low_stock = $stock->available_sheets <= $stock->low_stock_threshold_sheets;

            return $stock;
        });

        return view('inventory.index', compact('stocks'));
    }

    public function create(): View
    {
        return view('inventory.create', [
            'paperTypes' => PaperType::orderBy('name')->get(),
        ]);
    }

    public function store(StorePaperStockRequest $request): RedirectResponse
    {
        $payload = array_merge($request->validated(), [
            'tenant_id' => Tenant::query()->value('id'),
            'low_stock_threshold_sheets' => $request->integer('low_stock_threshold_sheets', config('printing.inventory.low_stock_sheet_threshold')),
        ]);

        PaperStock::create($payload);

        return redirect()->route('inventory.index')->with('success', 'Paper stock created successfully.');
    }

    public function show(PaperStock $inventory): View
    {
        return view('inventory.show', ['stock' => $inventory]);
    }

    public function edit(PaperStock $inventory): View
    {
        return view('inventory.edit', [
            'stock' => $inventory,
            'paperTypes' => PaperType::orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePaperStockRequest $request, PaperStock $inventory): RedirectResponse
    {
        $inventory->update($request->validated());

        return redirect()->route('inventory.index')->with('success', 'Paper stock updated successfully.');
    }

    public function destroy(PaperStock $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Paper stock deleted successfully.');
    }

    public function deductForProduction(Request $request, JobOrder $jobOrder): RedirectResponse
    {
        $request->validate([
            'total_sheets' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $jobOrder): void {
            $stock = PaperStock::query()
                ->where('tenant_id', $jobOrder->tenant_id)
                ->where('paper_type_id', $jobOrder->paper_type_id)
                ->where('gsm', $jobOrder->gsm)
                ->where('sheet_size', $jobOrder->standard_sheet_size)
                ->lockForUpdate()
                ->firstOrFail();

            $requiredSheets = $request->integer('total_sheets') ?: (int) optional($jobOrder->calculation()->latest('computed_at')->first())->total_sheets;
            $availableSheets = $stock->available_sheets;

            if ($requiredSheets < 1) {
                abort(422, 'No calculation sheets found.');
            }

            if ($availableSheets < $requiredSheets) {
                $shortfall = $requiredSheets - $availableSheets;
                PurchaseOrder::create([
                    'tenant_id' => $jobOrder->tenant_id,
                    'supplier_id' => \App\Models\Supplier::query()->value('id'),
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
                    'notes' => 'Auto-generated for sheet shortfall: ' . $shortfall,
                ]);

                abort(422, 'Insufficient paper stock. Suggested purchase order created for shortfall of ' . $shortfall . ' sheets.');
            }

            $remainingSheets = $availableSheets - $requiredSheets;
            $stock->update([
                'stock_reams' => intdiv($remainingSheets, 500),
                'stock_quires' => intdiv($remainingSheets % 500, 25),
                'stock_sheets' => $remainingSheets % 25,
            ]);

            if ($stock->fresh()->available_sheets <= $stock->low_stock_threshold_sheets) {
                SendLowStockAlertJob::dispatch($stock->id);
            }

            PaperStockMovement::create([
                'tenant_id' => $jobOrder->tenant_id,
                'paper_stock_id' => $stock->id,
                'job_order_id' => $jobOrder->id,
                'movement_type' => 'stock_out',
                'sheets' => $requiredSheets,
                'remarks' => 'Auto deduction on production start',
                'created_by' => $request->user()?->id,
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Stock deducted successfully for production.');
    }
}
