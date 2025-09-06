<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Reservation;

class ReservationWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('lapangan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_penyewa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_reservasi')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->width(50)
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ]);
            

            
    }
}
