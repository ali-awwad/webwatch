<?php

namespace App\Actions;

use App\Enums\Status;
use App\Models\Variation;
use App\Models\Check;
use App\Models\Certificate;
use App\DTOs\HttpStatusResultDTO;
use Illuminate\Support\Str;

class CreateCheckAction
{
    public static function execute(Variation $variation, Status|HttpStatusResultDTO $status, Certificate|null $certificate, string|null $errorMessage)
    {
        $redirectTo = null;
        $notes = null;
        if($status instanceof HttpStatusResultDTO) {
            if($status->redirectTo) {
                // if Location is http not then make a note
                if(str_starts_with($status->redirectTo, 'http://')) {
                    // note with alert emoji
                    $notes = '🚨 Redirect to http';
                }
                $redirectTo = Str::afterLast($status->redirectTo, 'https://');
                $redirectTo = rtrim($redirectTo, '/');
            }
            $status = match ($status->status) {
                200 => Status::UP,
                301 => Status::REDIRECT,
                302 => Status::REDIRECT,
                404 => Status::NOT_FOUND,
                500 => Status::DOWN,
                403 => Status::FORBIDDEN,
                'Failed to open connection' => Status::DOWN,
                default => Status::UNKNOWN,
            };
        }

        $website = $variation->website;

        $website->certificate_id = $certificate?->id;
        $website->last_status = $status->value;
        $website->redirect_to = $redirectTo;
        if($notes) {
            $website->notes = $notes;
        }
        $website->save();

        //number_of_retries
        // find last check
        $lastCheck = Check::whereVariationId($variation->id)->latest()->first();

        // if last check was success, and new check is success, update the last check timestamp
        if ($lastCheck && $lastCheck->status === Status::UP && $status === Status::UP) {
            $lastCheck->updated_at = now();
            $lastCheck->notes = $errorMessage;
            $lastCheck->save();
        }
        // if last check was not success, increment if it is exactly the same status
        else if ($lastCheck && $lastCheck->status === $status) {
            $lastCheck->number_of_retries++;
            $lastCheck->notes = $errorMessage;
            $lastCheck->save();
        }
        // then it is first check or new fail status of different type
        else {
            Check::create([
                'variation_id' => $variation->id,
                'status' => $status->value,
                'number_of_retries' => 0,
                'notes' => $errorMessage,
            ]);
        }
    }
}
