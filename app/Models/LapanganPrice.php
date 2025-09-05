<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LapanganPrice extends Model
{
    protected $fillable = [
        'lapangan_id',
        'day_type',
        'start_time',
        'end_time',
        'role',
        'price_per_hour',
    ];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }
}
