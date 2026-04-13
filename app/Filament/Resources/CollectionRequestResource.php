<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionRequestResource\Pages;
use App\Models\CollectionRequest;
use App\Models\User;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CollectionRequestResource extends Resource
{
    protected static ?string $model = CollectionRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Opérations';
    protected static ?string $navigationLabel = 'Demandes de ramassage';
    protected static ?string $modelLabel = 'Demande';
    protected static ?string $pluralModelLabel = 'Demandes de ramassage';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Détails de la demande')->schema([
                Forms\Components\TextInput::make('reference')
                    ->label('Référence')
                    ->disabled(),
                Forms\Components\Select::make('citizen_id')
                    ->label('Citoyen')
                    ->relationship('citizen', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('collector_id')
                    ->label('Collecteur assigné')
                    ->options(User::collectors()->active()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('zone_id')
                    ->label('Zone')
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'assigned' => 'Assigné',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'rejected' => 'Rejeté',
                    ])
                    ->required(),
                Forms\Components\Select::make('waste_type')
                    ->label('Type de déchets')
                    ->options([
                        'household' => 'Déchets ménagers',
                        'organic' => 'Déchets organiques',
                        'recyclable' => 'Recyclables',
                        'hazardous' => 'Déchets dangereux',
                        'construction' => 'Débris construction',
                        'other' => 'Autre',
                    ])
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->label('Priorité')
                    ->options([
                        'low' => 'Basse',
                        'normal' => 'Normale',
                        'high' => 'Haute',
                        'urgent' => 'Urgente',
                    ])
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Localisation')->schema([
                Forms\Components\Textarea::make('address')
                    ->label('Adresse complète')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')->numeric(),
                Forms\Components\TextInput::make('longitude')->numeric(),
            ])->columns(2),

            Forms\Components\Section::make('Notes & Planification')->schema([
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Planifié pour'),
                Forms\Components\TextInput::make('amount')
                    ->label('Montant (HTG)')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'free' => 'Gratuit',
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                    ]),
                Forms\Components\Textarea::make('cancellation_reason')
                    ->label('Raison annulation')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réf.')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('citizen.name')
                    ->label('Citoyen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('collector.name')
                    ->label('Collecteur')
                    ->placeholder('Non assigné')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'secondary' => 'pending',
                        'primary' => 'assigned',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'rejected']),
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'En attente',
                        'assigned' => 'Assigné',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priorité')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'low' => 'Basse',
                        'normal' => 'Normale',
                        'high' => 'Haute',
                        'urgent' => 'Urgente',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('waste_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'household' => 'Ménagers',
                        'organic' => 'Organiques',
                        'recyclable' => 'Recyclables',
                        'hazardous' => 'Dangereux',
                        'construction' => 'Construction',
                        'other' => 'Autre',
                        default => $state,
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zone')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Note')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'assigned' => 'Assigné',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priorité')
                    ->options([
                        'low' => 'Basse',
                        'normal' => 'Normale',
                        'high' => 'Haute',
                        'urgent' => 'Urgente',
                    ]),
                Tables\Filters\SelectFilter::make('waste_type')
                    ->label('Type de déchets')
                    ->options([
                        'household' => 'Ménagers',
                        'organic' => 'Organiques',
                        'recyclable' => 'Recyclables',
                        'hazardous' => 'Dangereux',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign')
                    ->label('Assigner')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (CollectionRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Select::make('collector_id')
                            ->label('Sélectionner un collecteur')
                            ->options(User::collectors()->active()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (CollectionRequest $record, array $data) {
                        $record->update([
                            'collector_id' => $data['collector_id'],
                            'status' => 'assigned',
                            'assigned_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCollectionRequests::route('/'),
            'create' => Pages\CreateCollectionRequest::route('/create'),
            'edit' => Pages\EditCollectionRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
