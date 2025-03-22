<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CompaniesWebsitesChart extends ChartWidget
{
    protected static ?string $heading = 'Companies by Website Count';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $companies = Company::withCount('websites')
            ->orderByDesc('websites_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Websites Count',
                    'data' => $companies->pluck('websites_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(231, 233, 237, 0.7)',
                        'rgba(136, 204, 119, 0.7)',
                        'rgba(250, 187, 61, 0.7)',
                        'rgba(106, 59, 87, 0.7)',
                    ],
                ],
            ],
            'labels' => $companies->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
} 