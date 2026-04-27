<?php

namespace App\Jobs;

use App\Models\PaperStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendLowStockAlertJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $paperStockId)
    {
    }

    public function handle(): void
    {
        $stock = PaperStock::find($this->paperStockId);

        if (! $stock) {
            return;
        }

        if ($stock->available_sheets <= $stock->low_stock_threshold_sheets) {
            Log::warning('Low stock alert generated', [
                'paper_stock_id' => $stock->id,
                'available_sheets' => $stock->available_sheets,
                'threshold' => $stock->low_stock_threshold_sheets,
            ]);
        }
    }
}
