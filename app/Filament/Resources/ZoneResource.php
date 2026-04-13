<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'Zones';
    protected static ?string $modelLabel = 'Zone';
    protected static ?string $pluralModelLabel = 'Zones';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Zone géographique')->schema([
                Forms\Components\TextInput::make('name')->label('Nom de la zone')->required(),
                Forms\Components\TextInput::make('commune')->label('Commune')->required(),
                Forms\Components\TextInput::make('department')->label('Département')->required(),
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                Forms\Components\Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')->numeric()->nullable(),
                Forms\Components\TextInput::make('longitude')->numeric()->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Zone')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('commune')->label('Commune')->searchable(),
                Tables\Columns\TextColumn::make('department')->label('Département')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
                Tables\Columns\TextColumn::make('collection_requests_count')
                    ->label('Demandes')
                    ->counts('collectionRequests')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
