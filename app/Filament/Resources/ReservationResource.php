<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use App\Models\Lapangan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Reservasi Lapangan';
    protected static ?string $pluralLabel = 'Reservasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal_reservasi')
                    ->label('Tanggal Reservasi')
                    ->required()
                    ->native(false)
                    ->reactive(),

                 Forms\Components\TextInput::make('nama_penyewa')
                    ->label('Penyewa')
                    ->required()
                    ->required(),

                Forms\Components\Select::make('lapangan_id')
                    ->label('Lapangan')
                    ->options(Lapangan::all()->pluck('nama', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('durasi_jam')
                    ->label('Durasi (jam)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(12)
                    ->reactive(),

                Forms\Components\Select::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->options(function ($get) {
                        $lapanganId = $get('lapangan_id');
                        $tanggal = $get('tanggal_reservasi');
                        $durasi = $get('durasi_jam');

                        if (!$lapanganId || !$tanggal || !$durasi) {
                            return [];
                        }

                        $jamBuka = 6;   // contoh jam buka lapangan
                        $jamTutup = 24; // contoh jam tutup lapangan

                        $reserved = Reservation::where('lapangan_id', $lapanganId)
                            ->where('tanggal_reservasi', $tanggal)
                            ->get();

                        $options = [];
                        for ($jam = $jamBuka; $jam <= $jamTutup - $durasi; $jam++) {
                            $jamMulai = sprintf('%02d:00:00', $jam);
                            $jamSelesai = sprintf('%02d:00:00', $jam + $durasi);

                            // cek bentrok dengan reservasi lain
                            $bentrok = $reserved->contains(function ($r) use ($jamMulai, $jamSelesai) {
                                return !(
                                    $jamSelesai <= $r->jam_mulai ||
                                    $jamMulai >= $r->jam_selesai
                                );
                            });

                            if (!$bentrok) {
                                $options[$jamMulai] = $jamMulai . ' - ' . $jamSelesai;
                            }
                        }

                        return $options;
                    })
                    ->required()
                    ->reactive(),

                Forms\Components\Placeholder::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->content(function ($get) {
                        if ($get('jam_mulai') && $get('durasi_jam')) {
                            return Carbon::parse($get('jam_mulai'))
                                ->addHours((int) $get('durasi_jam'))
                                ->format('H:i');
                        }
                        return '-';
                    }),
                
                    Forms\Components\Placeholder::make('total_harga_preview')
    ->label('Total Biaya')
    ->content(function ($get) {
        if (! $get('lapangan_id') || ! $get('durasi_jam') || ! $get('jam_mulai') || ! $get('tanggal_reservasi')) {
            return 'Isi data reservasi dulu';
        }

        $durasi = (int) $get('durasi_jam');
        $jamMulai = Carbon::parse($get('jam_mulai'));
        $role = (int) (auth()->user()->role ?? 2);

        $dayOfWeek = Carbon::parse($get('tanggal_reservasi'))->dayOfWeek;
        $dayType = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? 'weekday' : 'weekend';

        $harga = \App\Models\LapanganPrice::where('lapangan_id', $get('lapangan_id'))
            ->where('day_type', $dayType)
            ->where('role', $role)
            ->where('start_time', '<=', $jamMulai->format('H:i:s'))
            ->where('end_time', '>', $jamMulai->format('H:i:s'))
            ->first();

        $hargaPerJam = $harga?->price_per_hour ?? 0;
        return 'Rp ' . number_format($hargaPerJam * $durasi, 0, ',', '.');
    })
    ->reactive(),


                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(2)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_reservasi')->label('Tanggal')->date(),
                Tables\Columns\TextColumn::make('lapangan.nama')->label('Lapangan'),
                Tables\Columns\TextColumn::make('jam_mulai')->label('Mulai')->time(),
                Tables\Columns\TextColumn::make('jam_selesai')->label('Selesai')->time(),
                Tables\Columns\TextColumn::make('durasi_jam')->label('Durasi (jam)'),
                Tables\Columns\TextColumn::make('total_harga')->label('Total Harga')->money('IDR'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
