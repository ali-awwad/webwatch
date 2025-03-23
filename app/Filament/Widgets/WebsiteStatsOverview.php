<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Models\Certificate;
use App\Models\Variation;
use App\Models\Website;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsiteStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalActiveWebsites = Website::whereIsSkipped(false)->has('variations')->count();
        $inActiveWebsites = Website::whereIsSkipped(true)->count();

        $upWebsites = Variation::mainWithStatus(Status::UP->value)->count();
        $downWebsites = Variation::mainWithStatus(Status::DOWN->value)->count();
        $redirects = Variation::mainWithStatus(Status::REDIRECT->value)->count();


        $sslIssues = Variation::whereIn('status', [Status::SSL_ISSUE->value, Status::SSL_EXPIRED->value, Status::SSL_EXPIRING_SOON->value])->count();

        // Calculate percentages
        $upPercentage = $totalActiveWebsites > 0 ? round(($upWebsites / ($totalActiveWebsites)) * 100) : 0;

        // calculate the percentage of redirects
        $redirectsPercentage = $totalActiveWebsites > 0 ? round(($redirects / ($totalActiveWebsites)) * 100) : 0;

        // expiring or expired ssl certificates
        // valid_to in the next 30 days ( or expired )
        $expiredOrExpiringSslCertificates = Certificate::where('valid_to', '<=', now()->addDays(60))->count();
        $expiredOrExpiringSslCertificatesPercentage = $totalActiveWebsites > 0 ? round(($expiredOrExpiringSslCertificates / ($totalActiveWebsites)) * 100) : 0;


        return [
            Stat::make('Total Websites', $totalActiveWebsites)
                ->description($inActiveWebsites > 0 ? $inActiveWebsites . ' inactive websites' : '')
                ->descriptionIcon('heroicon-o-globe-alt')
                ->icon('heroicon-o-globe-alt'),

            Stat::make('Up Websites', $upWebsites)
                ->description($upPercentage . '% of websites are up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->icon('heroicon-o-check-circle'),

            Stat::make('Redirects', $redirects)
                ->description($redirectsPercentage . '% of websites are redirects')
                ->color('success')
                ->icon('heroicon-o-arrow-right'),

            Stat::make('Down Websites', $downWebsites)
                ->description('Sites with connectivity issues')
                ->color($downWebsites > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),

            Stat::make('SSL Issues', $sslIssues)
                ->description('Sites with SSL certificate issues')
                ->color($sslIssues > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-shield-exclamation'),

            Stat::make('Expired or Expiring SSL Certificates', $expiredOrExpiringSslCertificates)
                ->description($expiredOrExpiringSslCertificatesPercentage . '% of websites have expired or expiring SSL certificates')
                ->color($expiredOrExpiringSslCertificates > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-shield-exclamation'),


        ];
    }
}
