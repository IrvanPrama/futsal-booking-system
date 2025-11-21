<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelExpiredReservations extends Command
{
    protected $signature = 'reservations:cancel-expired';
    protected $description = 'Cancel reservations that are for tomorrow or past dates without payment proof';

    public function handle()
    {
        $now = Carbon::now('Asia/Makassar');
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();

        $count = 0;

        // Ambil semua pending tanpa bukti transfer
        $reservations = Reservation::where('status', 'pending')
            ->whereNull('bukti_transfer')
            ->get();

        foreach ($reservations as $reservation) {
            $tanggal = $reservation->tanggal_reservasi;
            $jamMulai = $reservation->jam_mulai;

            // Waktu reservasi (tanggal + jam mulai)
            $startTime = Carbon::parse("$tanggal $jamMulai", 'Asia/Makassar');

            // CASE 1: Tanggal sudah lewat
            $isPastDate = $startTime->lessThan($now);

            // CASE 2: Reservasi tanggal besok
            $isTomorrow = $tanggal == $tomorrow;

            if ($isPastDate || $isTomorrow) {
                $reservation->update([
                    'status' => 'cancelled',
                    'canceled_at' => $now,
                ]);

                ++$count;
            }
        }

        $this->info("Cancelled {$count} expired reservations at {$now->format('Y-m-d H:i')}.");

        return 0;
    }
}
