<?php

namespace App\Filament\Resources\HostingResource\RelationManagers;

use App\Enums\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariationsRelationManager extends RelationManager
{
    protected static string $relationship = 'variations';

    public function form(Form $form): Form
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
                // status
                Forms\Components\Select::make('status')
                    ->options(Status::class),
                Forms\Components\Select::make('certificate_id')
                    ->relationship('certificate', 'name')
                    ->searchable(),
                Forms\Components\TextInput::make('redirect_to')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_main')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website.domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('certificate.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('redirect_to')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20)
                    ->tooltip(fn($record): string => $record->redirect_to ?? 'N/A')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_main')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('website')
                    ->relationship('website', 'domain'),
                Tables\Filters\TernaryFilter::make('is_main'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
