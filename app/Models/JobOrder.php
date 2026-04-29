<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RawMaterial;

class JobOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'job_number',
        'job_title',
        'customer_id',
        'created_by',
        'order_date',
        'due_date',
        'status',
        'gsm',
        'paper_type_id',
        'raw_material_id',
        'ink_type',
        'pantone_codes',
        'finish_type',
        'total_pages',
        'page_size',
        'custom_width',
        'custom_height',
        'total_copies',
        'standard_sheet_size',
        'colors',
        'printing_style',
        'estimated_material_cost',
        'estimated_plate_cost',
        'estimated_other_cost',
        'estimated_total_cost',
        'estimated_unit_price',
        'estimated_total_price',
        'design_source',
        'design_file_path',
        'design_file_name',
        'design_file_mime',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'due_date' => 'date',
            'custom_width' => 'decimal:2',
            'custom_height' => 'decimal:2',
            'estimated_material_cost' => 'decimal:2',
            'estimated_plate_cost' => 'decimal:2',
            'estimated_other_cost' => 'decimal:2',
            'estimated_total_cost' => 'decimal:2',
            'estimated_unit_price' => 'decimal:2',
            'estimated_total_price' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function calculation(): HasMany
    {
        return $this->hasMany(JobCalculation::class);
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(JobCalculation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(JobPayment::class);
    }

    public function plates(): HasMany
    {
        return $this->hasMany(Ctp::class);
    }

    public function paperMovements(): HasMany
    {
        return $this->hasMany(PaperStockMovement::class);
    }

    public function deliveryChallans(): HasMany
    {
        return $this->hasMany(DeliveryChallan::class);
    }
}
