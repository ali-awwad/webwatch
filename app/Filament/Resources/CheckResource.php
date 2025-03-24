<?php

namespace App\Filament\Resources;

use App\Enums\Status;
use App\Filament\Resources\CheckResource\Pages;
use App\Models\Check;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckResource extends Resource
{
    protected static ?string $model = Check::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('variation_id')
                    ->relationship('variation', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options(Status::class)
                    ->required(),
                Forms\Components\TextInput::make('number_of_retries')
                    ->default(0),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variation.name')
                    ->limit(40)
                    ->tooltip(fn($state): string => $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('variation.website.domain')
                    ->limit(40)
                    ->tooltip(fn($state): string => $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('variation.website.company.name')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn($state): string => $state)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('variation.website.company.division.name')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn($state): string => $state)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->limit(50)
                    ->tooltip(fn(Tables\Columns\TextColumn $column): string => $column->getState() ?? ''),
                Tables\Columns\TextColumn::make('number_of_retries')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->filtersLayout(Tables\Enums\FiltersLayout::Modal)
            ->filters([
                Tables\Filters\SelectFilter::make('variation')
                    ->multiple()
                    ->relationship('variation', 'name'),
                // variation.is_main
                Tables\Filters\SelectFilter::make('variation_is_main')
                    ->multiple()
                    ->options([
                        true => 'Main',
                        false => 'Sub',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        
                        return $query->whereHas('variation', function (Builder $query) use ($data) {
                            $query->whereIn('is_main', $data['values']);
                        });
                    }),
                
                Tables\Filters\SelectFilter::make('website')
                    ->multiple()
                    ->relationship('variation.website', 'domain'),
                Tables\Filters\SelectFilter::make('certificate')
                    ->multiple()
                    ->relationship('variation.certificate', 'name'),
                Tables\Filters\SelectFilter::make('company')
                    ->multiple()
                    ->relationship('variation.website.company', 'name'),
                Tables\Filters\SelectFilter::make('division')
                    ->multiple()
                    ->relationship('variation.website.company.division', 'name'),

                // status
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options(Status::class),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChecks::route('/'),
            'create' => Pages\CreateCheck::route('/create'),
            'edit' => Pages\EditCheck::route('/{record}/edit'),
        ];
    }
}
