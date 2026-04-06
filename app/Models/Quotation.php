<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'quote_number',
        'inquiry_date',
        'valid_until',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
        'created_by',
        'approved_at',
        'converted_to_order_id',
    ];

    protected function casts(): array
    {
        return [
            'inquiry_date' => 'date',
            'valid_until' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
