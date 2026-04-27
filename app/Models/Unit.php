<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'symbol',
        'category',
        'base_quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_quantity' => 'decimal:4',
        ];
    }
}
