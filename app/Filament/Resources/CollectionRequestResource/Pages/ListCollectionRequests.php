<?php

namespace App\Filament\Resources\CollectionRequestResource\Pages;

use App\Filament\Resources\CollectionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCollectionRequests extends ListRecords
{
    protected static string $resource = CollectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvelle demande'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Toutes'),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'pending'))
                ->badge(\App\Models\CollectionRequest::where('status', 'pending')->count()),
            'assigned' => Tab::make('Assignées')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'assigned')),
            'in_progress' => Tab::make('En cours')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'in_progress')),
            'completed' => Tab::make('Terminées')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'completed')),
        ];
    }
}
