<?php
// app/Console/Commands/CancelExpiredReservations.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;

class CancelExpiredReservations extends Command
{
    protected $signature = 'reservations:cancel-expired';
    protected $description = 'Cancel reservations without payment proof 12 hours before start time';

    public function handle()
    {
        $count = Reservation::where('status', 'pending')
            ->whereNull('bukti_transfer')
            ->get()
            ->filter(function ($reservation) {
                return $reservation->isExpired();
            })
            ->each(function ($reservation) {
                $reservation->update(['status' => 'cancelled']);
            })
            ->count();

        $this->info("Cancelled {$count} expired reservations.");
        return 0;
    }
}
