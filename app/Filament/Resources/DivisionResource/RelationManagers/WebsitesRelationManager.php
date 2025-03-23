<?php

namespace App\Filament\Resources\DivisionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WebsitesRelationManager extends RelationManager
{
    protected static string $relationship = 'websites';

    protected static ?string $recordTitleAttribute = 'domain';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Usually, you wouldn't have create here since these come through companies
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No delete option as these are managed through companies
                ]),
            ]);
    }
} 