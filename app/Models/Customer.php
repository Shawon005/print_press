<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'billing_address',
        'delivery_address',
        'city',
        'outstanding_balance',
        'last_trade_at',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_balance' => 'decimal:2',
            'last_trade_at' => 'date',
        ];
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }
}
