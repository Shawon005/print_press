<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterialCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'description'];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'category_id');
    }
}
