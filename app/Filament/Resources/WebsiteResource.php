<?php

namespace App\Filament\Resources;

use App\Enums\Status;
use App\Filament\Resources\WebsiteResource\Pages;
use App\Models\Website;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                        Forms\Components\Select::make('certificate_id')
                            ->relationship('certificate', 'name'),
                        Forms\Components\TextInput::make('developer_team'),
                        Forms\Components\Select::make('hosting.name')
                            ->relationship('hosting', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('redirect_to'),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\ToggleButtons::make('is_waf_enabled')
                            ->label('Is WAF Enabled')
                            ->inline()
                            ->boolean(),
                        Forms\Components\TextInput::make('tech_stack'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_waf_enabled')
                    ->badge(),
                Tables\Columns\TextColumn::make('tech_stack')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('certificate.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hosting.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('redirect_to')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('developer_team')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('checks_count')
                    ->counts('checks')
                    ->label('Checks'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company')
                    ->relationship('company', 'name'),
                Tables\Filters\SelectFilter::make('certificate')
                    ->relationship('certificate', 'name'),
                Tables\Filters\SelectFilter::make('hosting')
                    ->relationship('hosting', 'name'),
                Tables\Filters\SelectFilter::make('last_status')
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
            'index' => Pages\ListWebsites::route('/'),
            'create' => Pages\CreateWebsite::route('/create'),
            'edit' => Pages\EditWebsite::route('/{record}/edit'),
        ];
    }
}
