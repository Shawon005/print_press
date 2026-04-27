<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paper_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('paper_types', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
            }
            if (! Schema::hasColumn('paper_types', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (! Schema::hasColumn('paper_types', 'status')) {
                $table->string('status')->default('active')->after('code');
            }
            if (! Schema::hasColumn('paper_types', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        Schema::create('ink_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('pantone_code')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('standard_sheets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->decimal('width_in', 8, 2);
            $table->decimal('height_in', 8, 2);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->string('category')->default('general');
            $table->decimal('base_quantity', 12, 4)->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('standard_sheets');
        Schema::dropIfExists('ink_types');

        Schema::table('paper_types', function (Blueprint $table): void {
            if (Schema::hasColumn('paper_types', 'tenant_id')) {
                $table->dropConstrainedForeignId('tenant_id');
            }
            $dropColumns = [];
            foreach (['code', 'status', 'notes'] as $column) {
                if (Schema::hasColumn('paper_types', $column)) {
                    $dropColumns[] = $column;
                }
            }
            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
