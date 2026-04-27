<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StandardSheet extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'width_in',
        'height_in',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'width_in' => 'decimal:2',
            'height_in' => 'decimal:2',
        ];
    }
}
