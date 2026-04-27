<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCalculation extends Model
{
    protected $fillable = [
        'job_order_id',
        'pages_per_sheet',
        'raw_sheets',
        'wastage_percentage',
        'wastage_sheets',
        'total_sheets',
        'reams',
        'quires',
        'remainder_sheets',
        'input_snapshot',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'wastage_percentage' => 'decimal:2',
            'input_snapshot' => 'array',
            'computed_at' => 'datetime',
        ];
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }
}
