<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringCertificatesWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Certificates Expiring Soon')
            ->description('SSL certificates that are about to expire or already expired')
            ->query(
                Certificate::query()
                    ->where('expires_at', '<=', now()->addDays(30000))
                    ->orderBy('expires_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => 
                        $record->expires_at->isPast()
                            ? 'danger'
                            : ($record->expires_at->diffInDays(now()) <= 7 
                                ? 'danger' 
                                : 'warning'
                            )
                    ),
                Tables\Columns\TextColumn::make('websites.domain')
                    //->counts('websites')
                    // comma separates websites by their domain
                    ->limit(150)
                    ->label('Affected Websites'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated(false);
    }
} 