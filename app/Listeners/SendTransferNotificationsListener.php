<?php

namespace App\Listeners;

use App\Events\TransferCompletedEvent;
use App\Http\Enums\NotificationStatusEnum;
use App\Jobs\TransferNotificationJob;

class SendTransferNotificationsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransferCompletedEvent $event): void
    {

        $transfer = $event->transfer;

        $transfer->load(['payerWallet', 'payeeWallet']);

        $payeeNotification = $event->transfer->notifications()->create([
            'user_id' => $transfer->payeeWallet->user_id,
            'transfer_id' => $transfer->id,
            'type' => 'payee',
            'status' => NotificationStatusEnum::PENDING,
            'message' => "You received a transfer of {$transfer->amount} from {$transfer->payerWallet->user->getFullName()}",
        ]);

        $payerNotification = $transfer->notifications()->create([
            'user_id' => $transfer->payerWallet->user_id,
            'transfer_id' => $transfer->id,
            'type' => 'payee',
            'status' => NotificationStatusEnum::PENDING,
            'message' => "Your transfer of {$transfer->amount} was sended to {$transfer->payeeWallet->user->getFullName()}",

        ]);

        TransferNotificationJob::dispatch($payeeNotification);
        TransferNotificationJob::dispatch($payerNotification);
    }
}
