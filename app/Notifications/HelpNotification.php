<?php

namespace App\Notifications;

use App\Traits\Silentable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class HelpNotification extends Notification
{
    use Queueable, Silentable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $commands = trans('bot.help.commands');
        if ($notifiable->is_admin) {
            $commands = trans('bot.help.admin_commands');
        }
        $notification = TelegramMessage::create()
            ->to($notifiable->chat_id)
            ->content(trans('bot.help.description')."\n\n".$commands);
        $notification = $this->silentize($notification,$notifiable);
        return $notification;
    }

}
