<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LapanganPriceResource\Pages;
use App\Filament\Resources\LapanganPriceResource\RelationManagers;
use App\Models\LapanganPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Redirect;

class LapanganPriceResource extends Resource
{
    protected static ?string $model = LapanganPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Lapangan';
    protected static ?string $navigationGroup = 'Manajemen';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->role === 0;
    }

    public static function canAccess(): bool
    {
        if (! auth()->check() || auth()->user()->role !== 0) {
            // Redirect ke dashboard
            Redirect::to('/admin');
        }

        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('lapangan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('day_type')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('end_time')
                    ->required(),
                Forms\Components\TextInput::make('role')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price_per_hour')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lapangan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('day_type'),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('role')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_hour')
                    ->numeric()
                    ->sortable(),
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListLapanganPrices::route('/'),
            'create' => Pages\CreateLapanganPrice::route('/create'),
            'edit' => Pages\EditLapanganPrice::route('/{record}/edit'),
        ];
    }
}
