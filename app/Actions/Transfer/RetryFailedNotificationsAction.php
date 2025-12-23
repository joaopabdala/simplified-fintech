<?php

namespace App\Actions\Transfer;

use App\Http\Enums\NotificationStatusEnum;
use App\Jobs\TransferNotificationJob;
use App\Models\Notification;

class RetryFailedNotificationsAction
{
    public function __construct() {}

    public function execute(): void
    {
        Notification::where('status', NotificationStatusEnum::FAILED)
            ->chunk(100, function ($notifications) {
                foreach ($notifications as $notification) {
                    TransferNotificationJob::dispatch($notification);
                }
            });
    }
}
