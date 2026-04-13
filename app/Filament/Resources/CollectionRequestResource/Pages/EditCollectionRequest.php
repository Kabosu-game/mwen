<?php

namespace App\Filament\Resources\CollectionRequestResource\Pages;

use App\Filament\Resources\CollectionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionRequest extends EditRecord
{
    protected static string $resource = CollectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
