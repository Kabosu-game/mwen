<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Opérations';
    protected static ?string $navigationLabel = 'Signalements';
    protected static ?string $modelLabel = 'Signalement';
    protected static ?string $pluralModelLabel = 'Signalements';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Détails')->schema([
                Forms\Components\TextInput::make('reference')->label('Référence')->disabled(),
                Forms\Components\Select::make('reporter_id')
                    ->label('Signalé par')
                    ->relationship('reporter', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('assigned_to')
                    ->label('Agent assigné')
                    ->options(User::collectors()->active()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('zone_id')
                    ->label('Zone')
                    ->relationship('zone', 'name')
                    ->nullable(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'illegal_dump' => 'Dépôt sauvage',
                        'blocked_canal' => 'Canal bouché',
                        'risk_zone' => 'Zone à risque',
                        'flooding' => 'Inondation',
                        'public_health' => 'Risque sanitaire',
                        'other' => 'Autre',
                    ])
                    ->required(),
                Forms\Components\Select::make('severity')
                    ->label('Sévérité')
                    ->options([
                        'low' => 'Faible',
                        'medium' => 'Moyenne',
                        'high' => 'Haute',
                        'critical' => 'Critique',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'reviewed' => 'Examiné',
                        'in_progress' => 'En cours',
                        'resolved' => 'Résolu',
                        'closed' => 'Fermé',
                        'rejected' => 'Rejeté',
                    ])
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Description')->schema([
                Forms\Components\TextInput::make('title')->label('Titre')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('description')->label('Description')->rows(3)->required()->columnSpanFull(),
                Forms\Components\Textarea::make('address')->label('Adresse')->rows(2)->required()->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')->numeric(),
                Forms\Components\TextInput::make('longitude')->numeric(),
            ])->columns(2),

            Forms\Components\Section::make('Notes admin')->schema([
                Forms\Components\Textarea::make('admin_notes')->label('Notes administrateur')->rows(3)->columnSpanFull(),
                Forms\Components\DateTimePicker::make('resolved_at')->label('Résolu le'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('reporter.name')->label('Signalé par')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Titre')->limit(40)->searchable(),
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
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => fn ($state) => in_array($state, ['high', 'critical']),
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'low' => 'Faible',
                        'medium' => 'Moyenne',
                        'high' => 'Haute',
                        'critical' => 'Critique',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'secondary' => 'pending',
                        'primary' => 'reviewed',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'En attente',
                        'reviewed' => 'Examiné',
                        'in_progress' => 'En cours',
                        'resolved' => 'Résolu',
                        'closed' => 'Fermé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('zone.name')->label('Zone')->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Type')->options([
                    'illegal_dump' => 'Dépôt sauvage',
                    'blocked_canal' => 'Canal bouché',
                    'risk_zone' => 'Zone à risque',
                    'flooding' => 'Inondation',
                    'public_health' => 'Sanitaire',
                ]),
                Tables\Filters\SelectFilter::make('severity')->label('Sévérité')->options([
                    'low' => 'Faible', 'medium' => 'Moyenne', 'high' => 'Haute', 'critical' => 'Critique',
                ]),
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
                    'pending' => 'En attente', 'in_progress' => 'En cours', 'resolved' => 'Résolu',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Report $r) => !in_array($r->status, ['resolved', 'closed']))
                    ->action(fn (Report $r) => $r->update(['status' => 'resolved', 'resolved_at' => now()])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('severity', ['high', 'critical'])
            ->where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
