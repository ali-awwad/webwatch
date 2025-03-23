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
