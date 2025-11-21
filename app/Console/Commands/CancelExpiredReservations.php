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
        $besok = $now->copy()->addDay()->toDateString();

        $count = Reservation::where('status', 'pending')
            ->whereNull('bukti_transfer')
            ->whereDate('tanggal_reservasi', $besok)
            ->update([
                'status' => 'cancelled',
                'canceled_at' => $now,
            ]);

        $this->info("Cancelled {$count} pending reservations for {$besok} at {$now->format('H:i')}.");

        return 0;
    }
}
