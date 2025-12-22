<?php

namespace App\Services\Notification;


use App\Services\Notification\Adapters\MockedNotificationAdapter;

class NotificationProvider
{
    public static function make()
    {
        $provider = config('services.notifications.provider');

        return match ($provider) {
            'local' => app(MockedNotificationAdapter::class),
            default => throw new \Exception("Provider '{$provider}' not supported")
        };
    }
}
