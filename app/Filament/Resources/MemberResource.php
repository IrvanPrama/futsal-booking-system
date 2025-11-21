<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status')
                    ->default('pending')
                    ->disabled()
                    ->dehydrated(true),

                Forms\Components\TextInput::make('user_id')
                    ->default(fn () => Auth::id())
                    ->required()
                    ->disabled()
                    ->dehydrated(true),

                Forms\Components\TextInput::make('nama_lengkap')
                    ->default(fn () => Auth::user()?->name)
                    ->required()
                    ->disabled()
                    ->dehydrated(true),

                Forms\Components\TextInput::make('alamat')
                    ->required(),

                Forms\Components\TextInput::make('no_wa')
                    ->default(fn () => Auth::user()?->wa)
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->default(fn () => Auth::user()?->email)
                    ->email()
                    ->required()
                    ->disabled()
                    ->dehydrated(true),

                Forms\Components\DatePicker::make('tanggal_lahir')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_wa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('acc')
    ->label('ACC')
    ->color('success')
    ->icon('heroicon-o-check-circle')
    ->visible(fn ($record) => $record->status !== 'acc' && auth()->user()->role == 0)
    ->requiresConfirmation()
    ->action(function ($record) {
        // CARI USER berdasarkan email
        $existingUser = User::where('email', $record->email)->first();

        if ($existingUser) {
            // Jika user sudah ada → update
            $existingUser->update([
                'name' => $record->nama_lengkap,
                'email' => $record->email,
                'wa' => $record->no_wa,
                'role' => 1,
            ]);

            // Update member
            $record->update([
                'user_id' => $existingUser->id,
                'status' => 'acc',
            ]);
        } else {
            // Jika user belum ada → buat baru
            $newUser = User::create([
                'name' => $record->nama_lengkap,
                'email' => $record->email,
                'wa' => $record->no_wa,
                'role' => 1,
                'password' => bcrypt('12345678'),
            ]);

            $record->update([
                'user_id' => $newUser->id,
                'status' => 'acc',
            ]);
        }
    }),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
