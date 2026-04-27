<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\PaperStock;
use App\Models\PaperStockMovement;
use App\Models\PurchaseOrder;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $monthlyRevenue = JobOrder::query()
            ->selectRaw('DATE_FORMAT(order_date, "%Y-%m") as month, SUM(estimated_total_price) as total')
            ->whereNotNull('order_date')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $paperConsumption = PaperStockMovement::query()
            ->where('movement_type', 'stock_out')
            ->selectRaw('paper_stock_id, SUM(sheets) as consumed')
            ->groupBy('paper_stock_id')
            ->with('stock.paperType')
            ->get();

        $statusBreakdown = JobOrder::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $outstandingPurchases = PurchaseOrder::query()->where('due_amount', '>', 0)->sum('due_amount');
        $lowStockCount = PaperStock::all()->filter(fn (PaperStock $s) => $s->available_sheets <= $s->low_stock_threshold_sheets)->count();

        return view('reports.index', compact('monthlyRevenue', 'paperConsumption', 'statusBreakdown', 'outstandingPurchases', 'lowStockCount'));
    }
}
