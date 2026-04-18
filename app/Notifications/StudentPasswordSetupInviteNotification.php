<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentPasswordSetupInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $setupUrl,
        private readonly string $studentName,
        private readonly string $expiryLabel
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Set your MLGCL account password')
            ->greeting('Hello ' . $this->studentName . ',')
            ->line('Your student account has been created. Set your password to activate your login.')
            ->action('Set Password', $this->setupUrl)
            ->line('This link expires on ' . $this->expiryLabel . '.')
            ->line('If you did not expect this invite, please contact your school administrator.');
    }
}

