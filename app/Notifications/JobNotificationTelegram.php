<?php

namespace App\Notifications;

use App\Traits\Silentable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class JobNotificationTelegram extends Notification
{
    use Queueable, Silentable;

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
        $desc = substr($this->item['description'],0,4000);
        if ($desc != $this->item['description']) {
            $desc .= trans('bot.more_desc');
        }
        $content = '<b>'.$this->item['title']."</b>\n\n".
            $desc."\n\n".
            '#'.str_replace(' ','-',$this->title);
        $notification = TelegramMessage::create()
            ->to($notifiable->chat_id)
            ->content($content)
            ->button(trans('bot.rss.view_job'), $this->item['link'])
            ->button(trans('bot.rss.apply_job'), $this->item['apply_link'])
            ->options(['parse_mode' => 'HTML']);
        $notification = $this->silentize($notification,$notifiable);
        return $notification;
    }
}
