<?php

namespace App\Filament\Resources\LapanganPriceResource\Pages;

use App\Filament\Resources\LapanganPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLapanganPrice extends EditRecord
{
    protected static string $resource = LapanganPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
