<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CancelExpiredReservations::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        file_put_contents(
            storage_path('logs/schedule_debug.log'),
            'Scheduler loaded at '.now().PHP_EOL,
            FILE_APPEND
        );

        // Jalankan setiap hari jam 13:50 waktu server (Asia/Makassar)
        $schedule->command('reservations:cancel-expired')
            ->timezone('Asia/Makassar')
            ->dailyAt('14:50')
            // ->everyMinute()
            ->appendOutputTo(storage_path('logs/scheduler.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
