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
        Schema::create('lapangan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapangan_id')->constrained('lapangans')->onDelete('cascade');
            $table->enum('day_type', ['weekday', 'weekend']); // weekday=Senin-Jumat, weekend=Sabtu-Minggu
            $table->time('start_time'); // contoh: 08:00:00
            $table->time('end_time');   // contoh: 12:00:00
            $table->tinyInteger('role'); // 0=admin,  1=member, 2=non-member
            $table->integer('price_per_hour'); // contoh: 150000.00  S
            $table->unique(['lapangan_id', 'day_type', 'start_time', 'end_time', 'role'], 'lapangan_price_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapangan_prices');
    }
};
