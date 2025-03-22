<?php

namespace App\Filament\Resources\WebsiteResource\RelationManagers;

use App\Models\Hosting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HostingsRelationManager extends RelationManager
{
    protected static string $relationship = 'hostings';

    // We need to override the getTableQuery since the hostings are accessed through variations
    protected function getTableQuery(): Builder
    {
        return Hosting::query()
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
                Forms\Components\TextInput::make('org')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('org')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // View-only relation since website-hosting relationship is now through variations
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for this view-only relation
            ]);
    }
} 