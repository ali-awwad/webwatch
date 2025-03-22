<?php

namespace App\Filament\Widgets;

use App\Models\TechStack;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TechStackChart extends ChartWidget
{
    protected static ?string $heading = 'Tech Stack Distribution';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = DB::table('tech_stack_website')
            ->join('tech_stacks', 'tech_stacks.id', '=', 'tech_stack_website.tech_stack_id')
            ->select('tech_stacks.name', DB::raw('count(*) as total'))
            ->groupBy('tech_stacks.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Tech Stacks',
                    'data' => $data->pluck('total')->toArray(),
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