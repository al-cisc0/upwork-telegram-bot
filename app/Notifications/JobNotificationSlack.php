<?php

namespace App\Notifications;

use App\Traits\Silentable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ActionsBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class JobNotificationSlack extends Notification
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
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $desc = strip_tags(substr($this->item['description'],0,2800));
        if ($desc != $this->item['description']) {
            $desc .= trans('bot.more_desc');
        }
        $notification = new SlackMessage();
        $notification->to($notifiable->chat_id)
            ->text($this->item['title'])
            ->headerBlock($this->item['title'])
            ->sectionBlock(function (SectionBlock $block) use ($desc) {
                $block->text($desc);
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) use ($desc) {
                $block->text($desc);
                $block->text('#'.str_replace(' ','-',$this->title));
            })
            ->actionsBlock(function (ActionsBlock $block) {
                $block->button(trans('bot.rss.view_job'))->url($this->item['link']);
                $block->button(trans('bot.rss.apply_job'))->url($this->item['apply_link']);
            });
        return $notification;
    }
}
