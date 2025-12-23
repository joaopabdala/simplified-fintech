<?php

namespace App\Services\Notification\Adapters;

use App\Services\Notification\NotificationInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class MockedNotificationAdapter implements NotificationInterface
{
    public function sendEmailMessage(string $email, string $message)
    {
        //        usleep(rand(100000, 500000));
        //
        //        if (rand(1, 3) === 1) {
        //            $errors = ['Internal Server Error', 'API Key Expired', 'Maintenance Mode'];
        //            throw new Exception($errors[array_rand($errors)]);
        //        }
        //
        Log::info("MockedNotificationAdapter: Message sent to $email: $message");

        return true;
    }
}
