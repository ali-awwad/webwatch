<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\CertificateResource\RelationManagers;
use App\Models\Division;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Certificate Information
                Forms\Components\Section::make('Certificate Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_to')
                            ->required(),
                        Forms\Components\TextInput::make('organization')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('issuer')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                // SANs
                Forms\Components\Section::make('SANs')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Repeater::make('sans')
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sans')
                    ->getStateUsing(fn($record) => is_array($record->sans) ? count($record->sans) : $record->sans)
                    ->searchable(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->dateTime('M j, Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_to')
                    ->dateTime('M j, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('organization')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->organization)
                    ->sortable(),
                Tables\Columns\TextColumn::make('issuer')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->issuer)
                    ->sortable(),
                Tables\Columns\TextColumn::make('websites.domain')
                    ->label('Used on')
                    //->getStateUsing(fn ($record) => '('.$record->websites->count() .') ' . $record->websites->pluck('domain')->implode(', '))
                    ->limit(150),
                Tables\Columns\TextColumn::make('websites_count')
                    ->counts('websites')
                    ->sortable()
                    ->label('Websites'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('valid_to', 'asc')
            ->filters([
                
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
            RelationManagers\WebsitesRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }
}
