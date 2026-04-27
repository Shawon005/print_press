<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaperStock extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'paper_type_id',
        'gsm',
        'sheet_size',
        'stock_reams',
        'stock_quires',
        'stock_sheets',
        'low_stock_threshold_sheets',
    ];

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PaperStockMovement::class);
    }

    public function getAvailableSheetsAttribute(): int
    {
        return ((int) $this->stock_reams * 500) + ((int) $this->stock_quires * 25) + (int) $this->stock_sheets;
    }
}
