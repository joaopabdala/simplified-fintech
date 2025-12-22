<?php

namespace App\Jobs;

use App\Http\Enums\NotificationStatusEnum;
use App\Models\Notification;
use App\Services\Notification\NotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TransferNotificationJob implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(protected Notification $notification)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationInterface $provider): void
    {
        $provider->sendEmailMessage(
            $this->notification->user->email,
            $this->notification->message
        );

        $this->notification->update([
           'status' => NotificationStatusEnum::SENT
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->notification->update([
            'status' => NotificationStatusEnum::FAILED,
            'error_log' => $exception->getMessage()
        ]);
    }
}
