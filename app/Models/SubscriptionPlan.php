<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'monthly_price',
        'yearly_price',
        'max_users',
        'max_orders_per_month',
        'max_warehouses',
        'max_storage_mb',
        'features_json',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'features_json' => 'array',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
