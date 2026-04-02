<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usages', function (Blueprint $table) {
            $table->unique(
                ['customer_id', 'month', 'year'],
                'usages_customer_month_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('usages', function (Blueprint $table) {
            $table->dropUnique('usages_customer_month_year_unique');
        });
    }
};