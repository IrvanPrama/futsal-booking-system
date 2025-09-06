<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Models\LapanganPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Exports\ExcelExport;
use App\Filament\Exports\ReservationsExport;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('lapangan_id')
                    ->label('Pilih Lapangan')
                    ->relationship('lapangan', 'nama')
                    ->required()
                    ->reactive(),

                TextInput::make('nama_penyewa')
                    ->required(),

                DatePicker::make('tanggal_reservasi')
                    ->required()
                    ->reactive()
                    ->minDate(now('Asia/Makassar')->toDateString()) // hanya bisa pilih mulai dari hari ini
                    ->native(false), // optional, supaya datepicker tampil rapi

                TextInput::make('durasi_jam')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->required()
                    ->reactive(),

                Select::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->options(function (callable $get) {
                        $lapanganId = $get('lapangan_id');
                        $tanggal    = \Carbon\Carbon::parse($get('tanggal_reservasi'))->toDateString();
                        $durasi     = $get('durasi_jam');

                        if (!$lapanganId || !$tanggal || !$durasi) {
                            return [];
                        }

                        $jamBuka  = 1;
                        $jamTutup = 24;

                        // Ambil reservasi lapangan tersebut
                        $reserved = \App\Models\Reservation::where('lapangan_id', $lapanganId)
                            ->where('tanggal_reservasi', $tanggal)
                            ->get();

                        $options   = [];
                        $now       = now('Asia/Makassar'); // jam sekarang WITA
                        $todayDate = $now->toDateString();

                        for ($jam = $jamBuka; $jam <= $jamTutup - $durasi; $jam++) {
                        $jamMulai   = sprintf('%02d:00:00', $jam);
                        $jamSelesai = sprintf('%02d:00:00', $jam + $durasi);

                        $jamMulaiCarbon = \Carbon\Carbon::parse($tanggal . ' ' . $jamMulai, 'Asia/Makassar');

                        // Skip kalau tanggal = hari ini
                        if ($tanggal == $todayDate) {
                            // Buat batas waktu jam ini + 30 menit
                            $limit = $jamMulaiCarbon->copy()->addMinutes(30);

                            // Kalau sekarang sudah lewat 30 menit setelah jam mulai → skip
                            if ($now->greaterThan($limit)) {
                                continue;
                            }
                        }

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

                    // nomor WA readonly, diambil dari user login
                TextInput::make('wa')
                    ->label('Nomor WhatsApp')
                    ->default(fn () => auth()->user()->wa)
                    ->disabled(),

                // total harga (readonly, dihitung otomatis)
                // TextInput::make('total_harga')
                //     ->label('Total Harga')
                //     ->disabled()
                //     ->dehydrated(true),
                TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->disabled()
                    ->dehydrated(true)
                    ->reactive()
                    ->afterStateUpdated(function ($set, $get) {
                        $durasi = (int) $get('durasi_jam');
                        $jamMulai = $get('jam_mulai');
                        $tanggal = $get('tanggal_reservasi');
                        $lapanganId = $get('lapangan_id');

                        if ($durasi && $jamMulai && $tanggal && $lapanganId) {
                            $jamCarbon = Carbon::parse($jamMulai);
                            $role = (int) (auth()->user()->role ?? 2); // default non-member
                            $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek;
                            $dayType = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? 'weekday' : 'weekend';

                            $harga = LapanganPrice::where('lapangan_id', $lapanganId)
                                ->where('day_type', $dayType)
                                ->where('role', $role)
                                ->where('start_time', '<=', $jamCarbon->format('H:i:s'))
                                ->where('end_time', '>', $jamCarbon->format('H:i:s'))
                                ->first();

                            $hargaPerJam = $harga?->price_per_hour ?? 0;

                            $set('total_harga', $hargaPerJam * $durasi);
                        }
                    }),


                // upload bukti transfer
                Forms\Components\FileUpload::make('bukti_transfer')
                    ->label('Upload Bukti Transfer')
                    ->image()
                    ->directory('bukti-transfer'),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'booked'  => 'Booked',
                        'pending' => 'Pending',
                        'cancel'  => 'Cancel',
                    ])
                    ->default('pending') // default untuk halaman create
                    ->disabled(fn () => ! auth()->check() || auth()->user()->role !== '0') // disabled kecuali admin (role=0)
                    ->required(fn () => auth()->check() && auth()->user()->role === '0') // wajib hanya kalau admin
                    ->dehydrated(true) // pastikan tetap disertakan saat submit meskipun disabled
                    ->reactive()
                    ->helperText('Status akan berubah ketika pembayaran anda sudah terverifikasi.'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lapangan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_penyewa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_reservasi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('durasi_jam')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_mulai'),
                Tables\Columns\TextColumn::make('jam_selesai'),
                Tables\Columns\TextColumn::make('total_harga')
                    ->money('idr', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'danger'  => 'cancel',
                        'success' => 'booked',
                    ])
                    ->sortable(),
                Tables\Columns\ImageColumn::make('bukti_transfer')
                    ->label('Bukti Transfer')
                    ->square()
                    ->height(50)
                    ->width(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
           ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(ReservationsExport::class), // pakai exporter yg kita buat
                ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $jamMulai   = Carbon::parse($data['jam_mulai']);
        $jamSelesai = $jamMulai->copy()->addHours($data['durasi_jam']);
        $data['jam_selesai'] = $jamSelesai->format('H:i:s');

        $role = auth()->user()->role;
        $dayOfWeek = Carbon::parse($data['tanggal_reservasi'])->dayOfWeek;
        $dayType = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? 'weekday' : 'weekend';

        $harga = LapanganPrice::where('lapangan_id', $data['lapangan_id'])
            ->where('day_type', $dayType)
            ->where('role', $role)
            ->where('start_time', '<=', $jamMulai->format('H:i:s'))
            ->where('end_time', '>', $jamMulai->format('H:i:s'))
            ->first();

        $hargaPerJam = $harga?->price_per_hour ?? 0;

        $data['total_harga'] = $hargaPerJam * (int) $data['durasi_jam'];
        $data['user_id'] = auth()->id();

        return $data;
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
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
