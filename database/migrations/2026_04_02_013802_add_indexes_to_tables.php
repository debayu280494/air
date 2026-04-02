<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ================= CUSTOMERS =================
        Schema::table('customers', function (Blueprint $table) {
            $table->index('name');
            $table->index('phone');
            $table->index('group_name');
            $table->index('status');
            $table->index('service_id');

            // untuk kombinasi filter
            $table->index(['group_name', 'status']);
        });

        // ================= USAGES =================
        Schema::table('usages', function (Blueprint $table) {
            $table->index('customer_id');

            // filter laporan bulanan
            $table->index(['month', 'year']);

            // query per customer + periode
            $table->index(['customer_id', 'month', 'year']);

            $table->index('status');
        });

        // ================= BILLS =================
        Schema::table('bills', function (Blueprint $table) {
            $table->index('customer_id');

            // laporan
            $table->index(['month', 'year']);

            // filter pembayaran
            $table->index('status');

            // laporan per customer + periode
            $table->index(['customer_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        // ================= CUSTOMERS =================
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['customers_name_index']);
            $table->dropIndex(['customers_phone_index']);
            $table->dropIndex(['customers_group_name_index']);
            $table->dropIndex(['customers_status_index']);
            $table->dropIndex(['customers_service_id_index']);
            $table->dropIndex(['customers_group_name_status_index']);
        });

        // ================= USAGES =================
        Schema::table('usages', function (Blueprint $table) {
            $table->dropIndex(['usages_customer_id_index']);
            $table->dropIndex(['usages_month_year_index']);
            $table->dropIndex(['usages_customer_id_month_year_index']);
            $table->dropIndex(['usages_status_index']);
        });

        // ================= BILLS =================
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex(['bills_customer_id_index']);
            $table->dropIndex(['bills_month_year_index']);
            $table->dropIndex(['bills_status_index']);
            $table->dropIndex(['bills_customer_id_month_year_index']);
        });
    }
};