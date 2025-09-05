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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapangan_id')->constrained('lapangans')->onDelete('cascade');
            $table->string('nama_penyewa');
            $table->date('tanggal_reservasi');
            $table->integer('durasi_jam'); // contoh: 2 jam
            $table->time('jam_mulai');     // hasil dari select
            $table->time('jam_selesai');   // auto terhitung dari jam_mulai + durasi
            $table->decimal('total_harga', 10, 2)->default(0);
            $table->enum('status', ['booked', 'pending', 'cancel'])->default('pending');
            $table->string('bukti_transfer')->nullable(); // path ke file bukti pembayaran
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
