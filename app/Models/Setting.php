<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'key', 'value_json'];

    protected function casts(): array
    {
        return [
            'value_json' => 'array',
        ];
    }
}
