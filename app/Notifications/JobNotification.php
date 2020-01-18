<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class JobNotification extends Notification
{
    use Queueable;

    /**
     * Title of feed
     *
     * @var string
     */
    protected $title = '';

    /**
     * UpworkJob details array
     *
     * @var array
     */
    protected $item = [];

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param array $item
     */
    public function __construct(string $title, array $item)
    {
        $this->title = $title;
        $this->item = $item;
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
        return TelegramMessage::create()
            ->to($notifiable->chat_id)
            ->content('*'.$this->item['title']."*\n\n".
                $this->item['description'])
            ->button(trans('bot.rss.view_job'), $this->item['link'])
            ->button(trans('bot.rss.apply_job'), $this->item['apply_link']);
    }
}
