<?php

namespace App\Http\Controllers;

use App\Notifications\HelpNotification;
use App\Notifications\MyIdNotification;
use App\Notifications\SimpleBotMessageNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookController extends Controller
{

    /**
     * User who interacts with bot
     *
     * @var null
     */
    protected $user = null;

    /**
     * Incoming message array
     *
     * @var array
     */
    protected $message = [];

    /**
     * Currently executing method name
     *
     * @var null
     */
    protected $mode = null;

    /**
     * Handle bot webhook and decide what to do next
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function setBotInput(Request $request, string $token)
    {
        if ($token != config('services.telegram-bot-api.token')) {
            return abort(403);
        }
        $this->message = $request->get('message');
        if (!empty($this->message['from']['is_bot'])){
            return response()->json([]);
        }
        $this->setUser();
        if ($this->user->is_banned) {
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.you_are_banned')));
            return response()->json([]);
        }
        if ($this->message && !empty($this->message['text'])) {
            if (!$this->mode) {
                $arr = explode('@',$this->message['text']);
                $methodName = 'execute'.ucfirst(Str::camel(str_replace('/','',$arr[0]))).'Command';
                if (method_exists($this, $methodName)) {
                    $this->$methodName();
                }
            } else {
                if ($this->message['text'] == 'cancel') {
                    $this->user->update([
                        'mode' => null
                    ]);
                    $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.canceled')));
                } else {
                    $this->{$this->mode}();
                }
            }
        }
//        Log::info(print_r($request->all(),1));
        return response()->json([]);
    }

    /**
     * Set current user who interacts with bot
     *
     */
    protected function setUser()
    {
        $telegramId = $this->message['from']['id'];
        $userName = '';
        if (!empty($this->message['from']['first_name'])) {
            $userName .= $this->message['from']['first_name'];
        }
        if (!empty($this->message['from']['last_name'])) {
            $userName .= ' '.$this->message['from']['last_name'];
        }
        $this->user = User::ofTelegramId($telegramId)->first();
        $isOwner = 0;
        $isActive = config('bot.free_access');
        if (config('bot.owner_id') == $telegramId) {
            $isOwner = 1;
            $isActive = 1;
        }
        if (!$this->user) {
            $this->user = User::create([
                'telegram_id' => $telegramId,
                'name' => $userName,
                'is_active' => $isActive,
                'is_admin' => $isOwner,
                'password' => Hash::make(Str::random(8))
            ]);
        } else {
            $this->mode = $this->user->mode;
        }
    }

    /**
     * Check if user has access to execute the command
     *
     * @return mixed
     */
    protected function checkUser()
    {
        if (!$this->user->is_active) {
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.request_access.description')));
        }
        return $this->user->is_active;
    }

    /**
     * Check if current user is admin
     *
     * @return mixed
     */
    protected function checkAdmin()
    {
        if (!$this->user->is_admin) {
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.admins_only')));
            $admins = User::isAdmin()->get();
            foreach ($admins as $admin) {
                $admin->chat_id = $admin->telegram_id;
                $admin->notify(new SimpleBotMessageNotification(trans('bot.admin_warning',[
                    'id' => $this->user->id,
                    'telegram_id' => $this->user->telegram_id,
                    'name' => $this->user->name
                ])));
            }
        }
        return $this->user->is_admin;
    }

    /**
     * Send response from bot to chat where command was executed
     *
     * @param Notification $notification
     */
    protected function sendBotResponse(Notification $notification)
    {
        $this->user->chat_id = $this->message['chat']['id'];
        $this->user->notify($notification);
    }

    /**
     * Send message about given user id is invalid
     *
     * @param string $commandMessage Additional message from command currently executing
     */
    protected function invalidUserIdResponse(?string $commandMessage = null)
    {
        $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.invalid_id')));
        if ($commandMessage) {
            $this->sendBotResponse(new SimpleBotMessageNotification($commandMessage));
        }
    }

    /**
     * Send message about given chat id is invalid
     *
     * @param string|null $commandMessage Additional message from command currently executing
     */
    protected function invalidChatIdResponse(?string $commandMessage = null)
    {
        $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.invalid_id')));
        if ($commandMessage) {
            $this->sendChatListing($commandMessage);
        }

    }

    /**
     * Change given user access state
     *
     * @param string $column What column to change 'is_active' or 'is_banned'
     * @param bool $access Grant or deny access
     * @param string $mode Message will be sent indicating is it currently grant or deny mode
     * @param string $result Message will be sent in result of command
     */
    protected function accessOperation(string $column, bool $access, string $mode, string $result)
    {
        if (!$this->checkUser() || !$this->checkAdmin()) {
            return;
        }
        $userId = trim($this->message['text']);
        if (!is_numeric($userId)) {
            $this->invalidUserIdResponse($mode);
        } else {
            $user = User::find($userId);
            if (!$user) {
                $this->invalidUserIdResponse($mode);
            } else {
                $user->update([$column => $access]);
                $user->chat_id = $user->telegram_id;
                $user->notify(new SimpleBotMessageNotification($result));
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.request_access.access_changed',[
                    'column' => str_replace('_','\\_',$column),
                    'id' => $user->id,
                    'name' => $user->name,
                    'state' => (int) $access
                ])));
                $this->user->update(['mode' => null]);
            }
        }
    }

    /**
     * Send chat listing with given header
     *
     * @param string $listingHeader Header of chats list
     */
    protected function sendChatListing(string $listingHeader)
    {
        $chats = $this->user->chats;
        if (!count($chats)) {
            $this->executeAddChatCommand();
        }
        $chatsListing = trans('bot.chat.listing');
        foreach ($chats as $chat) {
            $chatsListing .= $chat->id.' - '.$chat->title."\n";
        }
        $this->sendBotResponse(new SimpleBotMessageNotification($listingHeader."\n".$chatsListing));
    }

    /**
     * Send feed listing with given header
     *
     * @param string $listingHeader Header of chats list
     */
    protected function sendFeedListing(string $listingHeader)
    {
        $feeds = $this->user->feeds;
        if (!count($feeds)) {
            $this->sendBotResponse(new SimpleBotMessageNotification('bot.rss.you_have_no_feeds'));
            $this->user->update(['mode' => null]);
            return;
        }
        $feedListing = $listingHeader ? trans('bot.rss.listing') : '';
        foreach ($feeds as $feed) {
            $feedListing .= $feed->id.' - '.$feed->title."\n\n".$feed->link."\n\n";
        }
        $this->sendBotResponse(new SimpleBotMessageNotification($listingHeader."\n".$feedListing));
    }

    /**
     * Delete selected feed
     */
    protected function deleteFeed()
    {
        $value = trim($this->message['text']);
        $feed = $this->user->feeds()->where('id','=',$value)->first();
        if (!$feed) {
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.invalid_feed_id')));
            $this->sendFeedListing('');
        } else {
            $feed->delete();
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.feed_deleted')));
            $this->user->update(['mode' => null]);
        }
    }

    /**
     * Add new RSS feed
     */
    protected function addFeed()
    {
        $value = trim($this->message['text']);
        $feed = $this->user->feeds()->isEditing()->first();
        if (!$feed) {
            $feed = $this->newFeed();
            if (!$feed) {
                return;
            }
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.send_title')));
        } else if (!$feed->title) {
            $feed->update(['title' => substr($value,0,255)]);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.send_link')));
        } else if (!$feed->link) {
            $feed->update([
                'link' => $value,
                'is_editing' => 0
            ]);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.feed_added')));
            $this->user->update([
                'mode' => null
            ]);
        }
    }

    /**
     * New feed adding method
     *
     * @return |null
     */
    protected function newFeed()
    {
        $result = null;
        $value = trim($this->message['text']);
        if (!is_numeric($value)) {
            $this->invalidChatIdResponse();
        } else {
            $chat = $this->user->chats()->find($value);
            if (!$chat) {
                $this->invalidChatIdResponse(trans('bot.rss.send_chat_id'));
            } else {
                $result = $chat->feeds()->create([
                    'user_id' => $this->user->id,
                    'is_editing' => 1
                ]);
            }
        }
        return $result;
    }

    /**
     * Save to DB filters user set
     *
     * @param string $mode Mode string. Name of currently executing method.
     * @param string $type Type of filter. E.g. "Country"
     * @param string $message Message asking user to provide details
     * @param string $resultMessage Success message
     */
    protected function setFilters(string $mode, string $type, string $message, string $resultMessage)
    {
        if (!$this->checkUser()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => $mode]);
            $this->sendBotResponse(new SimpleBotMessageNotification($message));
        } else {
            if ($this->message['text'] == 'clear') {
                $this->user->filters()->where('type','=',$type)->delete();
            } else {
                $this->user->filters()->updateOrCreate(['type' => $type],['value' => $this->message['text']]);
            }
            $this->user->update(['mode' => null]);
            $this->sendBotResponse(new SimpleBotMessageNotification($resultMessage));
        }
    }

    /**
     * Toggle is_active state of all feeds connected to current chat
     * @param bool $state
     */
    protected function toggleConnectedFeeds(bool $state)
    {
        $chat = $this->user->chats()->where('chat_id', '=', $this->message['chat']['id'])->first();
        if (!$chat) {
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.chat.not_in_your_list')));
        } else {
            if ($state) {
                $chat->feeds()->update(['is_active' => 1]);
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.chat_feeds_enabled')));
            } elseif (!$state) {
                $chat->feeds()->update(['is_active' => 0]);
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.chat_feeds_disabled')));
            }

        }
    }
    // Bot Commands section

    /**
     * Send message with bot description and commands list
     *
     */
    protected function executeHelpCommand()
    {
        $this->sendBotResponse(new HelpNotification());
    }

    /**
     * Send greetings message
     */
    protected function executeStartCommand()
    {
        $this->sendBotResponse(new HelpNotification());
    }

    /**
     * Send his telegram id to user
     *
     */
    protected function executeMyTelegramIdCommand()
    {
        if ($this->checkUser()) {
            $this->sendBotResponse(new MyIdNotification());
        }
    }

    /**
     * Notify owner about new user asking to access the bot
     *
     */
    protected function executeRequestAccessCommand()
    {
        $owner = User::ofTelegramId(config('bot.owner_id'))->first();
        if (!$owner) {
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.request_access.owner_not_set')));
        } else {
            $owner->chat_id = $owner->telegram_id;
            $owner->notify(new SimpleBotMessageNotification(trans('bot.request_access.user_request',[
                'name' => $this->user->name,
                'id' => $this->user->id,
                'telegram_id' => $this->user->telegram_id
            ])));
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.request_access.request_sent')));
        }
    }

    /**
     * Give access to user
     */
    protected function executeGiveAccessCommand()
    {
        if (!$this->checkUser() || !$this->checkAdmin()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeGiveAccessCommand']);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.request_access.provide_id')));
        } else {
            $this->accessOperation('is_active',1,trans('bot.request_access.provide_id'),trans('bot.request_access.access_granted'));
        }

    }

    /**
     * Deny access for user
     */
    protected function executeDenyAccessCommand()
    {
        if (!$this->checkUser() || !$this->checkAdmin()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeDenyAccessCommand']);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.request_access.provide_id')));
        } else {
            $this->accessOperation('is_active',0,trans('bot.request_access.provide_id'),trans('bot.request_access.access_denied'));
        }

    }

    /**
     * Ban user permanently
     */
    protected function executeBanCommand()
    {
        if (!$this->checkUser() || !$this->checkAdmin()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeBanCommand']);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.ban.provide_id')));
        } else {
            $this->accessOperation('is_banned',1,trans('bot.ban.provide_id'),trans('bot.ban.banned'));
        }

    }

    /**
     * Unban user
     */
    protected function executeUnbanCommand()
    {
        if (!$this->checkUser() || !$this->checkAdmin()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeUnbanCommand']);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.ban.provide_id')));
        } else {
            $this->accessOperation('is_banned',0,trans('bot.ban.provide_id'),trans('bot.ban.unbanned'));
        }

    }

    /**
     * Add current chat to user's chat list
     */
    protected function executeAddChatCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        $title = $this->message['chat']['title'] ?? 'private';
        $this->user->chats()->updateOrCreate(['chat_id' => $this->message['chat']['id']],['title' => $title]);
        $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.chat.added',['title' => $title])));
    }

    /**
     * Delete current chat from list
     */
    protected function executeDeleteChatCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        $this->user->chats()->where('chat_id', '=', $this->message['chat']['id'])->delete();
        $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.chat.deleted')));
    }

    /**
     * Disable all feeds connected to current chat
     */
    protected function executeDisableChatCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        $this->toggleConnectedFeeds(0);
    }

    /**
     * Disable all feeds connected to current chat
     */
    protected function executeEnableChatCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        $this->toggleConnectedFeeds(1);
    }

    /**
     * Start RSS feed adding dialogue
     */
    protected function executeAddFeedCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeAddFeedCommand']);
            $this->sendChatListing(trans('bot.rss.send_chat_id'));
        } else {
            $this->addFeed();
        }
    }

    /**
     * Start RSS feed deleting dialogue
     */
    protected function executeDeleteFeedCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeDeleteFeedCommand']);
            $this->sendFeedListing(trans('bot.rss.send_feed_id'));
        } else {
            $this->deleteFeed();
        }
    }

    /**
     * List all user's feeds
     */
    protected function executeListFeedsCommand()
    {
        $this->sendFeedListing('');
    }

    /**
     * Set feeds update period
     */
    protected function executeSetPeriodCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeSetPeriodCommand']);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.send_period')));
        } else {
            $value = trim($this->message['text']);
            if (!is_numeric($value) || $value < 2) {
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.invalid_period')));
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.send_period')));
            } else {
                $this->user->feeds()->update([
                    'interval' => $value
                ]);
                $this->user->update(['mode' => null]);
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.rss.period_set',['interval' => $value])));
            }
        }
    }

    /**
     * Set countries list to exclude jobs from there
     */
    protected function executeFilterCountriesCommand()
    {
        $this->setFilters(
            'executeFilterCountriesCommand',
            'Country',
            trans('bot.filter.provide_countries'),
            trans('bot.filter.country_filter_set'));
    }

    /**
     * Set schedule when all notifications will be sent with no sound
     */
    protected function executeSilenceScheduleCommand()
    {
        if (!$this->checkUser()) {
            return;
        }
        if (!$this->mode) {
            $this->user->update(['mode' => 'executeSilenceScheduleCommand']);
            $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.sleep_mode.desc')));
        } else {
            if ($this->message['text'] == 'toggle') {
                $this->user->update(['enable_sleep' => !$this->user->enable_sleep,'mode' => null]);
                $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.sleep_mode.toggle',['state' => (int) $this->user->enable_sleep])));
            } else {
                $val = $this->message['text'];
                if (!preg_match('/^\d\d:\d\d-\d\d:\d\d$/',$val)) {
                    $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.sleep_mode.wrong_format')));
                    $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.sleep_mode.desc')));
                } else {
                    $arr = explode('-',$val);
                    $this->user->update([
                        'mode' => null,
                        'enable_sleep' => 1,
                        'sleep_from' => $arr[0],
                        'sleep_to' => $arr[1],
                        ]);
                    $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.sleep_mode.set')));
                }
            }
        }
    }

    /**
     * Set job title filter
     */
    protected function executeFilterTitleCommand()
    {
        $this->setFilters(
            'executeFilterTitleCommand',
            'Title',
            trans('bot.filter.provide_title_keywords'),
            trans('bot.filter.title_keywords_set'));
    }

    /**
     * Set job title filter
     */
    protected function executeFilterDescriptionCommand()
    {
        $this->setFilters(
            'executeFilterDescriptionCommand',
            'Description',
            trans('bot.filter.provide_description_keywords'),
            trans('bot.filter.description_keywords_set'));
    }

    /**
     * Disable all feeds
     */
    protected function executeDisableAllCommand()
    {
        $this->user->feeds()->update(['is_active' => 0]);
        $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.disable.all_disabled')));
    }

    /**
     * Enable all feeds
     */
    protected function executeEnableAllCommand()
    {
        $this->user->feeds()->update(['is_active' => 1]);
        $this->sendBotResponse(new SimpleBotMessageNotification(trans('bot.disable.all_enabled')));
    }
    // End of bot commands section

}
