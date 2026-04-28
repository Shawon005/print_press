<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('bill_to_name')->nullable()->after('due_amount');
            $table->string('bill_to_phone')->nullable()->after('bill_to_name');
            $table->string('bill_to_email')->nullable()->after('bill_to_phone');
            $table->text('bill_to_address')->nullable()->after('bill_to_email');
            $table->string('payment_method_title')->nullable()->after('bill_to_address');
            $table->string('bank_name')->nullable()->after('payment_method_title');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->text('terms_and_conditions')->nullable()->after('bank_account_number');
            $table->string('footer_phone')->nullable()->after('terms_and_conditions');
            $table->string('footer_email')->nullable()->after('footer_phone');
            $table->string('footer_address')->nullable()->after('footer_email');
            $table->string('signature_label')->nullable()->after('footer_address');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'bill_to_name',
                'bill_to_phone',
                'bill_to_email',
                'bill_to_address',
                'payment_method_title',
                'bank_name',
                'bank_account_number',
                'terms_and_conditions',
                'footer_phone',
                'footer_email',
                'footer_address',
                'signature_label',
            ]);
        });
    }
};
