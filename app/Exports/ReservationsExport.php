<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReservationsExport implements FromCollection
{
    public function collection()
    {
        return Reservation::all();
    }
}
