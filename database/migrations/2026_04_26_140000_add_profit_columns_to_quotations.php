<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->decimal('profit_percentage', 5, 2)->default(0)->after('tax');
            $table->decimal('profit_amount', 15, 2)->default(0)->after('profit_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropColumn(['profit_percentage', 'profit_amount']);
        });
    }
};
