<?php

namespace App\Helpers;

use App\DTOs\HttpStatusResultDTO;
use App\Enums\Status;
use Illuminate\Support\Str;

class GeneralHelper
{
    /**
     * Get status, redirect and notes from HttpStatusResultDTO used for Variation model
     * 
     * @param \App\DTOs\HttpStatusResultDTO $httpStatusWithRedirect
     * @return array [Status, string|null, string|null]
     */
    public static function getStatusAndRedirectAndNotes(HttpStatusResultDTO $httpStatusWithRedirect)
    {
        $redirectTo = null;
        $notes = null;

        if ($httpStatusWithRedirect->redirectTo) {
            // if Location is http not then make a note
            if (str_starts_with($httpStatusWithRedirect->redirectTo, 'http://')) {
                // note with alert emoji
                $notes = '🚨 Redirect to http';
            }
            $redirectTo = Str::afterLast($httpStatusWithRedirect->redirectTo, 'https://');
            $redirectTo = rtrim($redirectTo, '/');
        }

        $status = self::getStatus($httpStatusWithRedirect->status);

        return [$status, $redirectTo, $notes];
    }

    /**
     * Get status from HttpStatusResultDTO used for Website model
     * 
     * @param int|string $status
     * @return \App\Enums\Status
     */
    public static function getStatus(int|string $status): Status
    {
        $status = match ($status) {
            200 => Status::UP,
            301 => Status::REDIRECT,
            302 => Status::REDIRECT,
            404 => Status::NOT_FOUND,
            500 => Status::DOWN,
            403 => Status::FORBIDDEN,
            'Failed to open connection' => Status::DOWN,
            default => Status::UNKNOWN,
        };

        return $status;
    }
}
