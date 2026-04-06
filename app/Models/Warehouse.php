<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'address', 'manager_name', 'status'];

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
