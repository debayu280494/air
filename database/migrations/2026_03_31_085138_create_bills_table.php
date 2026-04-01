<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();

            // relasi ke usage
            $table->foreignId('usage_id')
                ->constrained('usages')
                ->cascadeOnDelete();

            // relasi ke customer
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->integer('month');
            $table->integer('year');

            // total tagihan
            $table->decimal('total_bill', 12, 2);

            // status pembayaran
            $table->enum('status', ['belum', 'lunas'])
                ->default('belum');

            $table->timestamps();

            // biar tidak ada tagihan dobel 1 periode
            $table->unique(['usage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
