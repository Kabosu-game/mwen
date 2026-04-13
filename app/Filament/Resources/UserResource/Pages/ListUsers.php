<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvel utilisateur'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous'),
            'citizens' => Tab::make('Citoyens')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'citizen')),
            'collectors' => Tab::make('Collecteurs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'collector')),
            'admins' => Tab::make('Admins')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('role', ['admin', 'super_admin'])),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\User::where('status', 'pending')->count()),
        ];
    }
}
