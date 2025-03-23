<?php

namespace App\Filament\Resources\HostingResource\RelationManagers;

use App\Models\Website;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WebsitesRelationManager extends RelationManager
{
    protected static string $relationship = 'websites';

    // For this relationship to work, we need to query websites through variations
    protected function getTableQuery(): Builder
    {
        // Use the website relationships through variations
        return Website::query()
            ->whereHas('variations', function ($query) {
                $query->where('hosting_id', $this->getOwnerRecord()->id);
            })
            ->distinct();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                // Since website no longer has direct hosting relationship, we don't need to include it
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_waf_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // We can't directly create a website from hosting anymore
                // The relation goes through Variation now
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Since it's not a direct relationship, we don't need bulk actions
            ]);
    }
} 