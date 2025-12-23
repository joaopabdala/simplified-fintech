<?php

namespace App\Services\Notification;

interface NotificationInterface
{
    public function sendEmailMessage(string $email, string $message);
}
