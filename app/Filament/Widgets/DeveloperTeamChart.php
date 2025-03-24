<?php

namespace App\Filament\Widgets;

use App\Filament\Traits\HasGlobalFilters;
use App\Models\DeveloperTeam;
use App\Models\Website;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DeveloperTeamChart extends ChartWidget
{
    use HasGlobalFilters;
    protected static ?string $heading = 'Developer Team Distribution';

    protected static ?int $sort = 50;

    protected function getData(): array
    {
        $websites = $this->getVariationsQuery()
        ->where('variations.is_main', true)
        ->select('developer_teams.name as developer_team_name', DB::raw('COUNT(websites.id) as count'))
        ->groupBy('developer_teams.name')
            ->orderByDesc('count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Developer Teams',
                    'data' => $websites->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#808080'
                    ],
                ],
            ],
            'labels' => $websites->pluck('developer_team_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
