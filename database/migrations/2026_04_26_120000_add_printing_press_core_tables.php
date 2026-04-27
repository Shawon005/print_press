<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->decimal('outstanding_balance', 15, 2)->default(0)->after('notes');
            $table->date('last_trade_at')->nullable()->after('outstanding_balance');
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->decimal('outstanding_payable', 15, 2)->default(0)->after('notes');
        });

        Schema::create('paper_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('supplier_paper_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('paper_type_id')->constrained('paper_types')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['supplier_id', 'paper_type_id']);
        });

        Schema::create('job_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('job_number');
            $table->string('job_title');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('order_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('gsm');
            $table->foreignId('paper_type_id')->constrained('paper_types')->restrictOnDelete();
            $table->string('ink_type')->default('CMYK');
            $table->string('pantone_codes')->nullable();
            $table->string('finish_type')->default('none');
            $table->unsignedInteger('total_pages');
            $table->string('page_size');
            $table->decimal('custom_width', 8, 2)->nullable();
            $table->decimal('custom_height', 8, 2)->nullable();
            $table->unsignedInteger('total_copies');
            $table->string('standard_sheet_size');
            $table->unsignedTinyInteger('colors')->default(4);
            $table->string('printing_style')->default('work_and_turn');
            $table->decimal('estimated_material_cost', 15, 2)->default(0);
            $table->decimal('estimated_plate_cost', 15, 2)->default(0);
            $table->decimal('estimated_other_cost', 15, 2)->default(0);
            $table->decimal('estimated_total_cost', 15, 2)->default(0);
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->decimal('estimated_total_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'job_number']);
        });

        Schema::create('job_calculations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->unsignedInteger('pages_per_sheet');
            $table->unsignedInteger('raw_sheets');
            $table->decimal('wastage_percentage', 5, 2);
            $table->unsignedInteger('wastage_sheets');
            $table->unsignedInteger('total_sheets');
            $table->unsignedInteger('reams');
            $table->unsignedInteger('quires');
            $table->unsignedInteger('remainder_sheets');
            $table->json('input_snapshot');
            $table->timestamp('computed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('paper_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('paper_type_id')->constrained('paper_types')->restrictOnDelete();
            $table->unsignedInteger('gsm');
            $table->string('sheet_size');
            $table->unsignedInteger('stock_reams')->default(0);
            $table->unsignedInteger('stock_quires')->default(0);
            $table->unsignedInteger('stock_sheets')->default(0);
            $table->unsignedInteger('low_stock_threshold_sheets')->default(2500);
            $table->timestamps();
            $table->unique(['tenant_id', 'paper_type_id', 'gsm', 'sheet_size'], 'uq_paper_stock_unique');
        });

        Schema::create('paper_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('paper_stock_id')->constrained('paper_stocks')->cascadeOnDelete();
            $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->enum('movement_type', ['stock_in', 'stock_out', 'adjustment']);
            $table->unsignedInteger('sheets');
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ctps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->string('plate_type');
            $table->string('plate_size');
            $table->unsignedInteger('quantity');
            $table->decimal('cost_per_plate', 15, 2);
            $table->decimal('total_plate_cost', 15, 2);
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('job_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->enum('payment_stage', ['advance', 'partial', 'final'])->default('advance');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('delivery_challans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('job_order_id')->constrained('job_orders')->cascadeOnDelete();
            $table->string('challan_number');
            $table->date('delivery_date');
            $table->string('receiver_name');
            $table->text('signature_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'challan_number']);
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreignId('job_order_id')->nullable()->after('warehouse_id')->constrained('job_orders')->nullOnDelete();
            $table->boolean('is_auto_suggested')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('job_order_id');
            $table->dropColumn('is_auto_suggested');
        });

        Schema::dropIfExists('delivery_challans');
        Schema::dropIfExists('job_payments');
        Schema::dropIfExists('ctps');
        Schema::dropIfExists('paper_stock_movements');
        Schema::dropIfExists('paper_stocks');
        Schema::dropIfExists('job_calculations');
        Schema::dropIfExists('job_orders');
        Schema::dropIfExists('supplier_paper_types');
        Schema::dropIfExists('paper_types');

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn('outstanding_payable');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn(['outstanding_balance', 'last_trade_at']);
        });
    }
};
