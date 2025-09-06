<?php

namespace App\Filament\Exports;

use App\Models\Reservation;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReservationsExport extends Exporter
{
    protected static ?string $model = Reservation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('lapangan_id')->label('Lapangan'),
            ExportColumn::make('nama_penyewa')->label('Nama Penyewa'),
            ExportColumn::make('tanggal_reservasi')->label('Tanggal Reservasi'),
            ExportColumn::make('durasi_jam')->label('Durasi (Jam)'),
            ExportColumn::make('jam_mulai')->label('Jam Mulai'),
            ExportColumn::make('jam_selesai')->label('Jam Selesai'),
            ExportColumn::make('total_harga')->label('Total Harga'),
            ExportColumn::make('user.name')->label('User'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('created_at')->label('Dibuat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        // opsi: return direct download url
        return 'Export selesai. Klik notifikasi untuk mengunduh file.';
    }

    // opsional: simpan di disk public supaya link bisa diakses
    public static function getDisk(): string
    {
        return 'public';
    }
}
