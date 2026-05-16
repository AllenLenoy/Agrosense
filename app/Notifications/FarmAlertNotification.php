<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Alert;

class FarmAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $alert;

    /**
     * Create a new notification instance.
     */
    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $priority = strtoupper($this->alert->priority);

        return (new MailMessage)
            ->subject("[{$priority} ALERT] {$this->alert->title}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new alert has been triggered for your farm: ' . $this->alert->farm->name)
            ->line('**Type:** ' . str_replace('_', ' ', $this->alert->type))
            ->line('**Message:** ' . $this->alert->message)
            ->action('View Dashboard', url('/dashboard'))
            ->line('Please take action as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'title' => $this->alert->title,
            'message' => $this->alert->message,
            'type' => $this->alert->type,
            'priority' => $this->alert->priority,
        ];
    }
}
