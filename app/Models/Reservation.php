<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** * The attributes that are mass assignable.
     * @var array<int, string> */
    protected $fillable = [
        'lapangan_id',
        'nama_penyewa',
        'tanggal_reservasi',
        'durasi_jam',
        'jam_mulai',
        'jam_selesai',
        'total_harga',
        'user_id',
        'status',
        'bukti_transfer',
        'catatan',
        'canceled_at',
    ];

    public function deadlinePayment()
    {
        return Carbon::parse($this->tanggal_reservasi.' '.$this->jam_mulai)
            ->subHours(12);
    }

    public function isExpired()
    {
        return Carbon::now('Asia/Makassar')->greaterThanOrEqualTo($this->deadlinePayment());
    }

    // setelah add code di atas, buat command di terminal: php artisan make:command CancelExpiredReservations
    // lalu isi file Console\Commands\CancelExpiredReservations.php
    // lalu buat schedule di app\Console\Kernel.php

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
