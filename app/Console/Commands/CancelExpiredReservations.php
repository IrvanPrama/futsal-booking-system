<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelExpiredReservations extends Command
{
    protected $signature = 'reservations:cancel-expired';
    protected $description = 'Cancel reservations without payment proof 12 hours before start time';

    public function handle()
    {
        $now = Carbon::now('Asia/Makassar');

        // Gabungkan tanggal_reservasi + jam_mulai lalu cek <= now + 12 jam
        $count = Reservation::where('status', 'pending')
            ->whereNull('bukti_transfer')
            ->whereRaw("STR_TO_DATE(CONCAT(tanggal_reservasi, ' ', jam_mulai), '%Y-%m-%d %H:%i:%s') <= ?", [
                $now->copy()->addHours(12)->toDateTimeString(),
            ])
            ->update([
                'status' => 'cancelled',
                'canceled_at' => $now,
            ]);

        $this->info("Cancelled {$count} expired reservations.");

        return 0;
    }
}
