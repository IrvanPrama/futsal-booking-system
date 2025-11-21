<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'status',
        'user_id',
        'nama_lengkap',
        'alamat',
        'no_wa',
        'email',
        'tanggal_lahir',
    ];
}
