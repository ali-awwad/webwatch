<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Models\Website;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ProblematicWebsitesWidget extends BaseWidget
{
    protected static ?string $model = Website::class;
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->heading('Problematic Websites')
            ->description('Websites with status issues')
            ->query(
                Website::query()
                    ->whereNotIn('last_status', [Status::UP->value])
                    ->latest('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->url(fn (Website $record): string => "https://{$record->domain}")
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('company.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    //->url(fn (Website $record): string => route('filament.control.resources.websites.edit', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated([5, 10, 25, 50]);
    }
} 