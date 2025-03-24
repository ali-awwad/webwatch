<?php

namespace App\Filament\Widgets;

use App\Filament\Traits\HasGlobalFilters;
use App\Models\TechStack;
use App\Models\Website;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TechStackChart extends ChartWidget
{
    use HasGlobalFilters;
    protected static ?string $heading = 'Tech Stack Distribution';
    
    protected static ?int $sort = 20;

    protected function getData(): array
    {
        $websites = $this->getVariationsQuery()
            ->where('variations.is_main', true)
            ->select('tech_stacks.name as tech_stack_name', DB::raw('COUNT(websites.id) as count'))
            ->groupBy('tech_stacks.name')
            ->orderByDesc('count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Tech Stacks',
                    'data' => $websites->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#808080'
                    ],
                ],
            ],
            'labels' => $websites->pluck('tech_stack_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
} 