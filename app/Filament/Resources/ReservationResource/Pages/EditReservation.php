<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\LapanganPrice;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    public function mount($record): void
    {
        // Jika bukan role 0, langsung redirect ke /admin
        if (auth()->user()->role !== 0) {
            Notification::make()
            ->title('Anda tidak memiliki akses ke halaman Hasil.')
            ->danger()
            ->send();

            $this->redirect('/admin');
        }

        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        return $data;
    }
}
