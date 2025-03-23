<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\Status;

class WebsitesRelationManager extends RelationManager
{
    protected static string $relationship = 'websites';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('developer_team_id')
                    ->relationship('developerTeam', 'name')
                    ->label('Developer Team')
                    ->searchable(),
                Forms\Components\Select::make('hosting_id')
                    ->relationship('hosting', 'name')
                    ->searchable(),
                Forms\Components\TextInput::make('redirect_to'),
                Forms\Components\Textarea::make('notes'),
                Forms\Components\ToggleButtons::make('is_waf_enabled')
                    ->label('Is WAF Enabled')
                    ->inline()
                    ->boolean(),
                Forms\Components\Select::make('techStacks')
                    ->relationship('techStacks', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('domain')
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_waf_enabled')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('techStacks.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('certificates.name'),
                Tables\Columns\TextColumn::make('hosting.name'),
                Tables\Columns\TextColumn::make('developerTeam.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('certificates')
                    ->relationship('certificates', 'name'),
                Tables\Filters\SelectFilter::make('is_waf_enabled')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ]),
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