<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Utilisateurs';
    protected static ?string $navigationLabel = 'Utilisateurs';
    protected static ?string $modelLabel = 'Utilisateur';
    protected static ?string $pluralModelLabel = 'Utilisateurs';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations personnelles')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                Forms\Components\Select::make('gender')
                    ->label('Genre')
                    ->options(['male' => 'Homme', 'female' => 'Femme', 'other' => 'Autre'])
                    ->nullable(),
                Forms\Components\DatePicker::make('birth_date')
                    ->label('Date de naissance')
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Compte')->schema([
                Forms\Components\Select::make('role')
                    ->label('Rôle')
                    ->options([
                        'citizen' => 'Citoyen',
                        'collector' => 'Collecteur',
                        'admin' => 'Administrateur',
                        'super_admin' => 'Super Admin',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                        'pending' => 'En attente',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\TextInput::make('points')
                    ->label('Points citoyens')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('balance')
                    ->label('Solde (HTG)')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponible (collecteurs)')
                    ->default(false),
            ])->columns(2),

            Forms\Components\Section::make('Localisation')->schema([
                Forms\Components\TextInput::make('commune')
                    ->label('Commune'),
                Forms\Components\TextInput::make('department')
                    ->label('Département'),
                Forms\Components\Textarea::make('address')
                    ->label('Adresse')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rôle')
                    ->colors([
                        'primary' => 'citizen',
                        'success' => 'collector',
                        'warning' => 'admin',
                        'danger' => 'super_admin',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'citizen' => 'Citoyen',
                        'collector' => 'Collecteur',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'secondary' => 'inactive',
                        'danger' => 'suspended',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                        'pending' => 'En attente',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('commune')
                    ->label('Commune')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'citizen' => 'Citoyens',
                        'collector' => 'Collecteurs',
                        'admin' => 'Administrateurs',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                        'pending' => 'En attente',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspendre')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status !== 'suspended')
                    ->action(fn (User $record) => $record->update(['status' => 'suspended'])),
                Tables\Actions\Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->status !== 'active')
                    ->action(fn (User $record) => $record->update(['status' => 'active'])),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
