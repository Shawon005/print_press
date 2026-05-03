<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DieLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'die_shape_id',
        'layout_mode',
        'sheet_width_mm',
        'sheet_height_mm',
        'box_count',
        'used_area_mm2',
        'wastage_area_mm2',
        'wastage_percent',
        'placements_json',
        'layout_svg',
    ];

    protected $casts = [
        'placements_json' => 'array',
    ];

    public function dieShape(): BelongsTo
    {
        return $this->belongsTo(DieShape::class);
    }
}
