<?php

namespace App\Filament\Widgets;

use App\Models\Division;
use App\Filament\Traits\HasGlobalFilters;
use App\Models\Website;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DivisionsWebsitesChart extends ChartWidget
{
    use HasGlobalFilters;
    protected static ?string $heading = 'Divisions by Website Count';

    protected static ?int $sort = 40;

    protected function getData(): array
    {
        $websites = $this->getVariationsQuery()
            ->where('variations.is_main', true)
            ->select('divisions.name as division_name', DB::raw('COUNT(websites.id) as count'))
            ->groupBy('divisions.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Websites Count',
                    'data' => $websites->pluck('count')->toArray(),
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
            'labels' => $websites->pluck('division_name')->toArray(),
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
