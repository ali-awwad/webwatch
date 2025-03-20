<?php

namespace App\Traits;


trait Enumable
{
    public static function values(): array
    {
        return collect(self::cases())->pluck('value')->toArray();
    }
}
