<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use App\Models\LapanganPrice;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $durasi = (int) $data['durasi_jam'];
        $jamMulai = Carbon::parse($data['jam_mulai']);
        $jamSelesai = $jamMulai->copy()->addHours($durasi);
        $data['jam_selesai'] = $jamSelesai->format('H:i:s');

        // ambil role user login (0=admin, 1=member, 2=non-member)
        $role = (int) (auth()->user()->role ?? 2);

        // weekday vs weekend
        $dayOfWeek = Carbon::parse($data['tanggal_reservasi'])->dayOfWeek;
        $dayType = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? 'weekday' : 'weekend';

        // cari harga di tabel lapangan_prices
        $harga = LapanganPrice::where('lapangan_id', $data['lapangan_id'])
            ->where('day_type', $dayType)
            ->where('role', $role)
            ->where('start_time', '<=', $jamMulai->format('H:i:s'))
            ->where('end_time', '>', $jamMulai->format('H:i:s'))
            ->first();

        $hargaPerJam = $harga?->price_per_hour ?? 0;

        // total harga
        $data['total_harga'] = $hargaPerJam * $durasi;

        // simpan user_id supaya tahu siapa yang reservasi
        $data['user_id'] = auth()->id();

        $data = parent::mutateFormDataBeforeCreate($data);

        // default status = pending
        $data['status'] = 'pending';

        return $data;
    }
}
