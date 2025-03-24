<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Filament\Traits\HasGlobalFilters;
use App\Models\Certificate;
use App\Models\Variation;
use App\Models\Website;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsiteStatsOverview extends BaseWidget
{
    use HasGlobalFilters;
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $totalWebsites = $this->getVariationsQuery()->count();
        $skippedWebsites = $this->getVariationsQuery()->whereIsSkipped(true)->count();

        $upWebsites = $this->getVariationsQuery()->where('status', Status::UP->value)->count();
        $downWebsites = $this->getVariationsQuery()->where('status', Status::DOWN->value)->count();
        $redirects = $this->getVariationsQuery()->where('status', Status::REDIRECT->value)->count();

        $sslIssues = $this->getVariationsQuery()->whereIn('status', [Status::SSL_ISSUE->value, Status::SSL_EXPIRED->value, Status::SSL_EXPIRING_SOON->value])->count();


        // Calculate percentages
        $upPercentage = $totalWebsites > 0 ? round(($upWebsites / ($totalWebsites)) * 100) : 0;

        // calculate the percentage of redirects
        $redirectsPercentage = $totalWebsites > 0 ? round(($redirects / ($totalWebsites)) * 100) : 0;

        // calculate the percentage of down websites
        $downPercentage = $totalWebsites > 0 ? round(($downWebsites / ($totalWebsites)) * 100) : 0;

        // calculate the percentage of ssl issues
        $sslIssuesPercentage = $totalWebsites > 0 ? round(($sslIssues / ($totalWebsites)) * 100) : 0;

        // expiring or expired ssl certificates
        // valid_to in the next 30 days ( or expired )
        $expiredOrExpiringSslCertificates = Certificate::where('valid_to', '<=', now()->addDays(30))->count();
        $expiredOrExpiringSslCertificatesPercentage = $totalWebsites > 0 ? round(($expiredOrExpiringSslCertificates / ($totalWebsites)) * 100) : 0;


        return [
            Stat::make('Total Websites', $totalWebsites)
                ->description($skippedWebsites > 0 ? $skippedWebsites . ' skipped websites' : '')
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
                ->description($downPercentage . '% of websites are down')
                ->color($downWebsites > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),

            Stat::make('SSL Issues', $sslIssues)
                ->description($sslIssuesPercentage . '% of websites have SSL certificate issues')
                ->color($sslIssues > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-shield-exclamation'),

            Stat::make('Expired or Expiring SSL Certificates', $expiredOrExpiringSslCertificates)
                ->description($expiredOrExpiringSslCertificatesPercentage . '% of certificates are expired or expiring in the next 30 days')
                ->color($expiredOrExpiringSslCertificates > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-shield-exclamation'),


        ];
    }
}
