<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Models\Website;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsiteStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $totalWebsites = Website::count();
        $upWebsites = Website::where('last_status', Status::UP->value)->count();
        $downWebsites = Website::whereIn('last_status', [Status::DOWN->value, Status::SSL_ISSUE->value])->count();
        $sslIssues = Website::whereIn('last_status', [Status::SSL_EXPIRED->value, Status::SSL_EXPIRING_SOON->value])->count();
        
        // Calculate percentages
        $upPercentage = $totalWebsites > 0 ? round(($upWebsites / $totalWebsites) * 100) : 0;
        
        return [
            Stat::make('Total Websites', $totalWebsites)
                ->description('All monitored websites')
                ->icon('heroicon-o-globe-alt'),
                
            Stat::make('Up Websites', $upWebsites)
                ->description($upPercentage . '% of websites are up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->icon('heroicon-o-check-circle'),
                
            Stat::make('Down Websites', $downWebsites)
                ->description('Sites with connectivity issues')
                ->color($downWebsites > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),
                
            Stat::make('SSL Issues', $sslIssues)
                ->description('Sites with SSL certificate issues')
                ->color($sslIssues > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-shield-exclamation'),
        ];
    }
} 