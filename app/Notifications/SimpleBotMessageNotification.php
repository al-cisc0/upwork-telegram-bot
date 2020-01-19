<?php

namespace App\Notifications;

use App\Traits\Silentable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class SimpleBotMessageNotification extends Notification
{
    use Queueable, Silentable;

    /**
     * Message to send
     *
     * @var string
     */
    protected $message = '';

    /**
     * Create a new notification instance.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {
        $notification = TelegramMessage::create()
            ->to($notifiable->chat_id)
            ->content($this->message);
        $notification = $this->silentize($notification,$notifiable);
        return $notification;
    }

}
