<?php

namespace App\Filament\Resources\WebsiteResource\RelationManagers;

use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificates';

    // We need to override the getTableQuery since the certificates are accessed through variations
    protected function getTableQuery(): Builder
    {
        return Certificate::query()
            ->whereHas('variations', function ($query) {
                $query->where('website_id', $this->getOwnerRecord()->id);
            })
            ->distinct();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('organization')
                    ->maxLength(255),
                Forms\Components\TextInput::make('issuer')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('valid_from'),
                Forms\Components\DateTimePicker::make('valid_to'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('organization')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('issuer')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('valid_to')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // View-only relation
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for this view-only relation
            ]);
    }
} 