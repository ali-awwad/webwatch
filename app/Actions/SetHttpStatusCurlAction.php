<?php

namespace App\Actions;

use App\DTOs\HttpStatusResultDTO;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SetHttpStatusCurlAction
{
    public static function execute(string $domain): HttpStatusResultDTO
    {
        $url = "https://$domain";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_NOBODY, true); // Only fetch headers
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            curl_close($ch);
            return new HttpStatusResultDTO(
                status: 'Failed to open connection',
                redirectTo: null,
            );
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        preg_match('/Location: (.*?)(\r|\n)/i', $response, $matches);
        $redirectTo = $matches[1] ?? null;

        Log::info('HTTP Source response headers: ' . json_encode($response));
        Log::info('HTTP Source status: ' . $httpCode);
        Log::info('HTTP Source redirectTo: ' . $redirectTo);
        
        curl_close($ch);

        // Process redirect URL
        if ($redirectTo && str_starts_with($redirectTo, '/')) {
            $redirectTo = rtrim($url, '/') . $redirectTo;
        } elseif ($redirectTo && (str_starts_with($redirectTo, 'http://') || str_starts_with($redirectTo, 'https://'))) {
            $redirectTo = Str::after($redirectTo, 'http://');
            $redirectTo = Str::after($redirectTo, 'https://');
        }

        return new HttpStatusResultDTO(
            status: $httpCode ?? "Unknown",
            redirectTo: $redirectTo,
        );
    }
}
