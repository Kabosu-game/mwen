<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'Plans d\'abonnement';
    protected static ?string $modelLabel = 'Plan';
    protected static ?string $pluralModelLabel = 'Plans d\'abonnement';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Plan')->schema([
                Forms\Components\TextInput::make('name')->label('Nom')->required(),
                Forms\Components\TextInput::make('slug')->label('Identifiant unique')->required()->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('price_monthly')->label('Prix mensuel (HTG)')->numeric()->required(),
                Forms\Components\TextInput::make('price_yearly')->label('Prix annuel (HTG)')->numeric()->nullable(),
                Forms\Components\TextInput::make('collections_per_month')
                    ->label('Collectes / mois (-1 = illimité)')
                    ->numeric()
                    ->default(-1),
                Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
                Forms\Components\TagsInput::make('features')
                    ->label('Fonctionnalités incluses')
                    ->placeholder('Ajouter une fonctionnalité')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Plan')->sortable(),
                Tables\Columns\TextColumn::make('price_monthly')->label('Mensuel (HTG)')->money('HTG')->sortable(),
                Tables\Columns\TextColumn::make('price_yearly')->label('Annuel (HTG)')->money('HTG')->placeholder('-'),
                Tables\Columns\TextColumn::make('collections_per_month')
                    ->label('Collectes/mois')
                    ->formatStateUsing(fn ($state) => $state === -1 ? 'Illimité' : $state),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
                Tables\Columns\TextColumn::make('subscriptions_count')->label('Abonnés')->counts('subscriptions'),
            ])
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
