<?php

namespace App\Models;

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
    ];

    

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
