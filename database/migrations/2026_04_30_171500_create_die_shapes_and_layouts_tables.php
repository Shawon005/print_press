<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('die_shapes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('source')->default('generated');
            $table->json('dimensions_mm')->nullable();
            $table->json('polygon_points_mm');
            $table->longText('svg_path')->nullable();
            $table->longText('svg_raw')->nullable();
            $table->timestamps();
        });

        Schema::create('die_layouts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('die_shape_id')->index();
            $table->string('layout_mode')->default('normal');
            $table->decimal('sheet_width_mm', 12, 3);
            $table->decimal('sheet_height_mm', 12, 3);
            $table->unsignedInteger('box_count')->default(0);
            $table->decimal('used_area_mm2', 15, 3)->default(0);
            $table->decimal('wastage_area_mm2', 15, 3)->default(0);
            $table->decimal('wastage_percent', 8, 3)->default(0);
            $table->json('placements_json');
            $table->longText('layout_svg')->nullable();
            $table->timestamps();

            $table->foreign('die_shape_id')->references('id')->on('die_shapes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('die_layouts');
        Schema::dropIfExists('die_shapes');
    }
};
