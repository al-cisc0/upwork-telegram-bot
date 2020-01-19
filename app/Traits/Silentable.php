<?php


namespace App\Traits;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

trait Silentable
{
    protected function silentize(TelegramMessage $notification, User $notifiable)
    {
        if ($notifiable->enable_sleep && $notifiable->sleep_from && $notifiable->sleep_to) {
            $from = Carbon::parse($notifiable->sleep_from);
            $to = Carbon::parse($notifiable->sleep_to);
            if ($to < $from) {
                $to->addDay();
            }
            if ($from <= Carbon::now() && $to >= Carbon::now()) {
                $notification->disableNotification();
            }
        }
        return $notification;
    }
}
