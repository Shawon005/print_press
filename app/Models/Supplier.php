<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'supplier_code',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'outstanding_payable',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_payable' => 'decimal:2',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function paperTypes(): BelongsToMany
    {
        return $this->belongsToMany(PaperType::class, 'supplier_paper_types');
    }
}
