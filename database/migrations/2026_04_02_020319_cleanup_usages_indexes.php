
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usages', function (Blueprint $table) {

            // 🔥 hapus index yang redundant
            try {
                $table->dropIndex('usages_customer_id_index');
            } catch (\Exception $e) {}

            try {
                $table->dropIndex('usages_customer_id_month_year_index');
            } catch (\Exception $e) {}

            // OPTIONAL: kalau kamu TIDAK pakai laporan bulanan global
            // bisa hapus ini juga
            /*
            try {
                $table->dropIndex('usages_month_year_index');
            } catch (\Exception $e) {}
            */
        });
    }

    public function down(): void
    {
        Schema::table('usages', function (Blueprint $table) {

            // balikin lagi kalau rollback
            $table->index('customer_id', 'usages_customer_id_index');

            $table->index(
                ['customer_id', 'month', 'year'],
                'usages_customer_id_month_year_index'
            );

            // optional restore
            /*
            $table->index(['month', 'year'], 'usages_month_year_index');
            */
        });
    }
};