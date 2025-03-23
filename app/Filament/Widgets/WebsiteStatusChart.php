<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Models\Check;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class WebsiteStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Website Status History';
    protected static ?int $sort = 60;
    protected int|string|array $columnSpan = 'full';
    
    protected function getData(): array
    {
        // Get data for the last 7 days
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->put(Carbon::now()->subDays($i)->format('Y-m-d'), 0);
        }
        
        // Count successes by day
        $successByDay = Check::where('status', Status::UP->value)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
            
        // Count failures by day
        $failureByDay = Check::whereIn('status', [Status::DOWN->value, Status::SSL_ISSUE->value, Status::SSL_EXPIRED->value, Status::SSL_EXPIRING_SOON->value])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
        
        // Merge dates with data
        $successData = $dates->merge($successByDay)->sortKeys();
        $failureData = $dates->merge($failureByDay)->sortKeys();
        
        return [
            'datasets' => [
                [
                    'label' => 'Successful Checks',
                    'data' => $successData->values()->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Failed Checks',
                    'data' => $failureData->values()->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $successData->keys()->toArray(),
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
} 