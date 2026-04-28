<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->string('design_source', 50)->nullable()->after('estimated_total_price');
            $table->string('design_file_path')->nullable()->after('design_source');
            $table->string('design_file_name')->nullable()->after('design_file_path');
            $table->string('design_file_mime', 120)->nullable()->after('design_file_name');
        });

        Schema::table('quotations', function (Blueprint $table): void {
            $table->string('design_source', 50)->nullable()->after('total');
            $table->string('design_file_path')->nullable()->after('design_source');
            $table->string('design_file_name')->nullable()->after('design_file_path');
            $table->string('design_file_mime', 120)->nullable()->after('design_file_name');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->dropColumn([
                'design_source',
                'design_file_path',
                'design_file_name',
                'design_file_mime',
            ]);
        });

        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropColumn([
                'design_source',
                'design_file_path',
                'design_file_name',
                'design_file_mime',
            ]);
        });
    }
};
