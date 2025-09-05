<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LapanganPrice;

class LapanganPriceSeeder extends Seeder
{
    public function run(): void
    {
        $lapanganId = 1; // ganti sesuai id lapangan Anda

        // contoh weekday (Senin - Jumat), member
        LapanganPrice::create([
            'lapangan_id' => $lapanganId,
            'day_type' => 'weekday',
            'start_time' => '06:00:00',
            'end_time' => '15:00:00',
            'role' => 1, // member
            'price_per_hour' => 135000,
        ]);

        // weekday, non-member
        LapanganPrice::create([
            'lapangan_id' => $lapanganId,
            'day_type' => 'weekday',
            'start_time' => '06:00:00',
            'end_time' => '15:00:00',
            'role' => 2, // non-member
            'price_per_hour' => 140000,
        ]);

        // lanjutkan sesuai tabel harga lain ...
    }
}
