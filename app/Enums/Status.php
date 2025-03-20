<?php

namespace App\Enums;

use App\Traits\Enumable;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum Status: string implements HasIcon, HasColor
{
    use Enumable;
    case UP = 'up';
    case DOWN = 'down';
    case SSL_ISSUE = 'ssl_issue';
    case SSL_EXPIRED = 'ssl_expired';
    case SSL_EXPIRING_SOON = 'ssl_expiring_soon';

    public function getIcon(): string
    {
        return match ($this) {
            self::UP => 'heroicon-o-check-circle',
            self::DOWN => 'heroicon-o-x-circle',
            self::SSL_ISSUE => 'heroicon-o-exclamation-circle',
            self::SSL_EXPIRED => 'heroicon-o-exclamation-circle',
            self::SSL_EXPIRING_SOON => 'heroicon-o-exclamation-circle',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UP => 'success',
            self::DOWN => 'danger',
            self::SSL_ISSUE => 'warning',
            self::SSL_EXPIRED => 'danger',
            self::SSL_EXPIRING_SOON => 'warning',
        };
    }
} 