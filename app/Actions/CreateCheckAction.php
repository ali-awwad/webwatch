<?php

namespace App\Actions;

use App\Enums\Status;
use App\Models\Variation;
use App\Models\Check;

class CreateCheckAction
{
    public static function execute(Variation $variation, Status $status, string|null $errorMessage)
    {
        //number_of_retries
        // find last check
        /**
         * @var \App\Models\Check $lastCheck
         */
        $lastCheck = Check::whereVariationId($variation->id)->latest()->first();

        // if last check was not success, increment if it is exactly the same status
        // but only for 15 minutes
        if ($lastCheck && $lastCheck->status === $status && $lastCheck->status !== Status::UP && $lastCheck->created_at->diffInMinutes() < 15) {
            $lastCheck->number_of_retries++;
            $lastCheck->notes = $errorMessage;
            $lastCheck->save();
            return;
        }
        // if last check was success, and new check is success, update the last check timestamp
        // or
        // then it is first check or new fail status of different type

        Check::create([
            'variation_id' => $variation->id,
            'status' => $status->value,
            'number_of_retries' => 0,
            'notes' => $errorMessage,
        ]);
    }
}
