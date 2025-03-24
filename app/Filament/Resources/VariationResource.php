<?php

namespace App\Filament\Resources;

use App\Enums\Status;
use App\Filament\Resources\VariationResource\Pages;
use App\Jobs\CheckWebsiteJob;
use App\Models\Variation;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class VariationResource extends Resource
{
    protected static ?string $model = Variation::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Website Information')
                    ->columns(2)
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
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->limit(40)
                    ->tooltip(fn ($state): string => $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('website.domain')
                    ->limit(40)
                    ->tooltip(fn ($state): string => $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('certificate.name')
                    ->limit(40)
                    ->tooltip(fn ($state): ?string => $state)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hosting.name')
                    ->limit(40)
                    ->tooltip(fn ($state): string => $state)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->badge(),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options(Status::class)
                    ->multiple(),
            ])
            ->filtersFormColumns(2)
            ->filtersLayout(FiltersLayout::Modal)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // check
                Tables\Actions\Action::make('check')
                    ->label('Check')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (Variation $record) {
                        CheckWebsiteJob::dispatch($record);
                        Notification::make()
                            ->title('Check Started')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('check')
                        ->label('Check')
                        ->color('success')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                CheckWebsiteJob::dispatch($record);
                            }
                        }),
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
