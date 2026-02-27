<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your EasyHCS Verification Code')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your two-factor authentication code is:')
            ->line('**' . $this->code . '**')
            ->line('This code expires in **10 minutes**.')
            ->line('If you did not request this code, please secure your account immediately.')
            ->salutation('EasyHCS Security');
    }
}