<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Filament\Traits\HasGlobalFilters;
use App\Models\Variation;
use App\Models\Website;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ProblematicWebsitesWidget extends BaseWidget
{
    use HasGlobalFilters;
    protected static ?string $model = Website::class;
    protected static ?int $sort = 70;
    protected int|string|array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->heading('Problematic Websites')
            ->description('Websites with status issues')
            ->query(
                $this->getVariationsQuery(false)
                    ->whereNotIn('status', [Status::UP->value, Status::REDIRECT->value])
                    ->with(['website', 'website.company'])
                    ->select('variations.*')
                    ->latest('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->limit(30)
                    ->tooltip(fn ($state): string => $state)
                    ->url(fn (Variation $record): string => "https://{$record->name}")
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('website.company.name'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->dateTimeTooltip()
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