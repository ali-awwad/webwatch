<?php

namespace App\Filament\Resources;

use App\Enums\Status;
use App\Filament\Resources\WebsiteResource\Pages;
use App\Jobs\CheckWebsiteJob;
use App\Models\Website;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Website Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('domain')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required(),
                        Forms\Components\Select::make('developer_team_id')
                            ->relationship('developerTeam', 'name')
                            ->label('Developer Team')
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->limit(30)
                    ->sortable()
                    ->tooltip(fn(Website $record): string => $record->domain)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('domain', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('company.name')
                    ->limit(20)
                    ->sortable()
                    ->toggleable()
                    ->tooltip(fn(Website $record): string => $record->company->name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('name', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('last_status')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_waf_enabled')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('techStacks.name')
                    ->badge()
                    ->limit(20)
                    ->sortable()
                    ->toggleable()
                    ->tooltip(fn(Website $record): string => $record->techStacks->pluck('name')->implode(', '))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('techStacks', function (Builder $query) use ($search): Builder {
                            return $query->where('name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('certificates.name')
                    ->limit(20)
                    ->sortable()
                    ->toggleable()
                    ->tooltip(fn(Website $record): string => $record->certificates->pluck('name')->implode(', ') ?: 'N/A')
                    ,
                Tables\Columns\TextColumn::make('hostings.name')
                    ->limit(20)
                    ->sortable()
                    ->toggleable()
                    ->tooltip(fn(Website $record): string => $record->hostings->pluck('name')->implode(', '))
                    ,
                Tables\Columns\TextColumn::make('redirect_to')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20)
                    ->tooltip(fn(Website $record): string => $record->redirect_to ?? 'N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('developerTeam.name')
                    ->limit(20)
                    ->sortable()
                    ->toggleable()
                    ->tooltip(fn(Website $record): string => $record->developerTeam?->name ?? 'N/A')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('developerTeam', function (Builder $query) use ($search): Builder {
                            return $query->where('name', 'like', "%{$search}%");
                        });
                    })
                    ,

                Tables\Columns\TextColumn::make('checks_count')
                    ->counts('checks')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->label('Checks'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(2)
            ->filtersLayout(Tables\Enums\FiltersLayout::Modal)
            ->filters([
                Tables\Filters\SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('certificates')
                    ->relationship('certificates', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('hostings')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('hostings', 'name'),
                Tables\Filters\SelectFilter::make('developerTeam')
                    ->relationship('developerTeam', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Developer Team'),
                Tables\Filters\SelectFilter::make('techStacks')
                    ->relationship('techStacks', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('last_status')
                    ->multiple()
                    ->options(Status::class),
                Tables\Filters\SelectFilter::make('is_waf_enabled')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('check')
                    ->action(function (Website $record) {
                        CheckWebsiteJob::dispatch($record);
                        Notification::make()
                        ->success()
                        ->title('Check Started')
                        ->send();
                    }),
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
            WebsiteResource\RelationManagers\ChecksRelationManager::class,
            WebsiteResource\RelationManagers\VariationsRelationManager::class,
            WebsiteResource\RelationManagers\CertificatesRelationManager::class,
            WebsiteResource\RelationManagers\HostingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebsites::route('/'),
            'create' => Pages\CreateWebsite::route('/create'),
            'edit' => Pages\EditWebsite::route('/{record}/edit'),
        ];
    }
}
