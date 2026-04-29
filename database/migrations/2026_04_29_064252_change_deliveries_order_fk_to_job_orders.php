<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove rows that do not point to an existing job order before adding FK.
        DB::table('deliveries')
            ->whereNotIn('order_id', function ($query) {
                $query->select('id')->from('job_orders');
            })
            ->delete();

        Schema::table('deliveries', function (Blueprint $table) {
            // Switch deliveries.order_id FK from orders.id to job_orders.id.
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('job_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }
};
