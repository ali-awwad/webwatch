<?php

namespace App\Actions;

use App\DTOs\HttpStatusResultDTO;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GetHttpStatusAction
{
    public static function execute(string $domain): HttpStatusResultDTO
    {
        $context = stream_context_create([
            "http" => [
                "method" => "GET",
                "ignore_errors" => true,  // Ensure we get headers for non-200 responses
                "follow_location" => 0,
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
            ]
        ]);

        $handle = @fopen("https://$domain", "r", false, $context);

        if ($handle === false) {
            
            return new HttpStatusResultDTO(
                status: 'Failed to open connection',
                redirectTo: null,
            );
        }

        $metaData = stream_get_meta_data($handle);
        Log::info('HTTP Source metaData: ' . json_encode($metaData));
        fclose($handle);

        if (isset($metaData["wrapper_data"][0])) {
            preg_match('/\d{3}/', $metaData["wrapper_data"][0], $matches);
            $redirectTo = collect($metaData["wrapper_data"])->first(fn($item) => str_contains($item, 'Location:')) ?? null;

            Log::info('HTTP Source redirectTo: ' . $redirectTo);

            // some redirects are like this: Location: /en
            if ($redirectTo && str_starts_with($redirectTo, 'Location: /')) {
                // remove Location: / and add the domain to the redirect
                $redirectTo = Str::after($redirectTo, 'Location: /');
                // make sure $domain ends with a / even if it doesn't have one
                if (!Str::endsWith($domain, '/')) {
                    $domain = $domain . '/';
                }
                $redirectTo = $domain . $redirectTo;
            } elseif ($redirectTo && str_starts_with($redirectTo, 'Location: https://')) {
                $redirectTo = Str::after($redirectTo, 'Location: https://');
            } elseif ($redirectTo && str_starts_with($redirectTo, 'Location: http://')) {
                $redirectTo = Str::after($redirectTo, 'Location: http://');
            }

            return new HttpStatusResultDTO(
                status: (int)$matches[0] ?? "Unknown",
                redirectTo: $redirectTo,
            );
        }

        return new HttpStatusResultDTO(
            status: "Failed to retrieve status",
            redirectTo: null,
        );
    }
}
