<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MissionResource\Pages;
use App\Models\Mission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MissionResource extends Resource
{
    protected static ?string $model = Mission::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Emplois Verts';
    protected static ?string $navigationLabel = 'Missions Travay Vèt';
    protected static ?string $modelLabel = 'Mission';
    protected static ?string $pluralModelLabel = 'Missions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Mission')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre de la mission')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'collection' => 'Collecte',
                        'cleaning' => 'Nettoyage',
                        'sorting' => 'Tri',
                        'awareness' => 'Sensibilisation',
                        'other' => 'Autre',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'open' => 'Ouverte',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ])
                    ->required(),
                Forms\Components\Select::make('zone_id')
                    ->label('Zone')
                    ->relationship('zone', 'name')
                    ->nullable(),
                Forms\Components\TextInput::make('slots')
                    ->label('Places disponibles')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('payment')
                    ->label('Rémunération (HTG)')
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Date début')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Date fin')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('address')
                    ->label('Adresse')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Détails supplémentaires')->schema([
                Forms\Components\TagsInput::make('requirements')
                    ->label('Critères requis')
                    ->placeholder('Ajouter un critère')
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('equipment_provided')
                    ->label('Équipements fournis')
                    ->placeholder('Ajouter un équipement')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(40),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'collection' => 'Collecte',
                        'cleaning' => 'Nettoyage',
                        'sorting' => 'Tri',
                        'awareness' => 'Sensibilisation',
                        'other' => 'Autre',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'secondary' => 'draft',
                        'primary' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Brouillon',
                        'open' => 'Ouverte',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('slots')
                    ->label('Places')
                    ->formatStateUsing(fn ($state, $record) => $record->slots_taken . '/' . $record->slots),
                Tables\Columns\TextColumn::make('payment')
                    ->label('Paiement')
                    ->money('HTG')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('zone.name')->label('Zone')->placeholder('-')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
                    'draft' => 'Brouillon', 'open' => 'Ouverte', 'in_progress' => 'En cours',
                    'completed' => 'Terminée', 'cancelled' => 'Annulée',
                ]),
                Tables\Filters\SelectFilter::make('type')->label('Type')->options([
                    'collection' => 'Collecte', 'cleaning' => 'Nettoyage',
                    'sorting' => 'Tri', 'awareness' => 'Sensibilisation',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('open')
                    ->label('Ouvrir')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Mission $m) => $m->status === 'draft')
                    ->action(fn (Mission $m) => $m->update(['status' => 'open'])),
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
            'index' => Pages\ListMissions::route('/'),
            'create' => Pages\CreateMission::route('/create'),
            'edit' => Pages\EditMission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'open')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
