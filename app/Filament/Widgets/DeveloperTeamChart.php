<?php

namespace App\Filament\Widgets;

use App\Models\DeveloperTeam;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DeveloperTeamChart extends ChartWidget
{
    protected static ?string $heading = 'Developer Team Distribution';
    
    protected static ?int $sort = 50;

    protected function getData(): array
    {
        // SQLSTATE[HY000]: General error: 1 HAVING clause on a non-aggregate query (Connection: sqlite, SQL: select "developer_teams".*, (select count(*) from "websites" where "developer_teams"."id" = "websites"."developer_team_id") as "websites_count" from "developer_teams" having "websites_count" > 0)
        // check if database is sqlite
        if(DB::getDriverName() === 'sqlite') {
            // how to do this in sqlite? without getting HAving clause non-aggregate query error
            $data = DeveloperTeam::withCount('websites')
            ->get();
        } else {
            // mysql
            $data = DeveloperTeam::withCount('websites')
            ->having('websites_count', '>', 0)
            ->get();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Developer Teams',
                    'data' => $data->pluck('websites_count')->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#808080'
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
} 