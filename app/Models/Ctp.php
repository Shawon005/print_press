<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ctp extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'job_order_id',
        'plate_type',
        'plate_size',
        'quantity',
        'cost_per_plate',
        'total_plate_cost',
        'issued_by',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_plate' => 'decimal:2',
            'total_plate_cost' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
