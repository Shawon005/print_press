<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_materials', function (Blueprint $table): void {
            $table->decimal('width_in', 8, 2)->nullable()->after('average_cost');
            $table->decimal('height_in', 8, 2)->nullable()->after('width_in');
        });

        Schema::table('job_orders', function (Blueprint $table): void {
            $table->foreignId('raw_material_id')->nullable()->after('paper_type_id')->constrained('raw_materials')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('raw_material_id');
        });

        Schema::table('raw_materials', function (Blueprint $table): void {
            $table->dropColumn(['width_in', 'height_in']);
        });
    }
};

