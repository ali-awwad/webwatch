<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VariationResource\Pages;
use App\Models\Variation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VariationResource extends Resource
{
    protected static ?string $model = Variation::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('website_id')
                    ->relationship('website', 'domain')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('certificate_id')
                    ->relationship('certificate', 'name')
                    ->searchable(),
                Forms\Components\Select::make('hosting_id')
                    ->relationship('hosting', 'name')
                    ->searchable(),
                Forms\Components\TextInput::make('redirect_to')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_main')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website.domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('certificate.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hosting.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('redirect_to')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20)
                    ->tooltip(fn(Variation $record): string => $record->redirect_to ?? 'N/A')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_main')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('website')
                    ->relationship('website', 'domain'),
                Tables\Filters\SelectFilter::make('hosting')
                    ->relationship('hosting', 'name'),
                Tables\Filters\TernaryFilter::make('is_main'),
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
        return [
            VariationResource\RelationManagers\ChecksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVariations::route('/'),
            'create' => Pages\CreateVariation::route('/create'),
            'edit' => Pages\EditVariation::route('/{record}/edit'),
        ];
    }
} 