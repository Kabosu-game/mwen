<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nouveau signalement')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous'),
            'pending' => Tab::make('En attente')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'pending'))
                ->badge(\App\Models\Report::where('status', 'pending')->count()),
            'critical' => Tab::make('Critiques')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('severity', ['high', 'critical'])->where('status', 'pending')),
            'in_progress' => Tab::make('En cours')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'in_progress')),
            'resolved' => Tab::make('Résolus')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'resolved')),
        ];
    }
}
