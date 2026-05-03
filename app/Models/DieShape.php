<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DieShape extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'source',
        'dimensions_mm',
        'polygon_points_mm',
        'svg_path',
        'svg_raw',
    ];

    protected $casts = [
        'dimensions_mm' => 'array',
        'polygon_points_mm' => 'array',
    ];
}
