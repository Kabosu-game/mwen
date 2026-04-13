<?php

namespace App\Filament\Resources\CollectionRequestResource\Pages;

use App\Filament\Resources\CollectionRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollectionRequest extends CreateRecord
{
    protected static string $resource = CollectionRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
