<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentReportsWidget extends BaseWidget
{
    protected static ?string $heading = 'Signalements récents';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()
                    ->whereIn('severity', ['high', 'critical'])
                    ->where('status', 'pending')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->copyable(),
                Tables\Columns\TextColumn::make('title')->label('Titre')->limit(40),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'illegal_dump' => 'Dépôt sauvage',
                        'blocked_canal' => 'Canal bouché',
                        'risk_zone' => 'Zone à risque',
                        'flooding' => 'Inondation',
                        'public_health' => 'Sanitaire',
                        'other' => 'Autre',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('severity')
                    ->label('Sévérité')
                    ->colors([
                        'warning' => 'high',
                        'danger' => 'critical',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'high' => 'Haute',
                        'critical' => 'Critique',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('reporter.name')->label('Signalé par'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->url(fn (Report $record) => route('filament.admin.resources.reports.edit', $record))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
