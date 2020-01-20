<?php

namespace App\Console\Commands;

use App\Feed;
use App\Notifications\JobNotification;
use App\Notifications\SimpleBotMessageNotification;
use App\Setting;
use App\User;
use App\UserFilter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RequestRssUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:update {--feed_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request RSS update from Upwork';

    /**
     * Search results feed array
     *
     * @var array
     */
    protected $feed = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->feed = [];
        if (!$feedId = $this->option('feed_id')) {
            return;
        }
        if (!$feed = Feed::find($feedId)) {
            return;
        }
        $rss = new \DOMDocument();
        try {
            $rss->load($feed->link);
            foreach ($rss->getElementsByTagName('item') as $node) {
                $hash = md5($node->getElementsByTagName('link')->item(0)->nodeValue);
                if ($feed->jobs()->ofHash($hash)->exists()) {
                    continue;
                }
                $description = $node->getElementsByTagName('description')->item(0)->nodeValue;
                $country = $this->getCountry($description);
                $applyLink = $this->getApplyLink($description);
                $description = $this->sanitize($description);

                $this->feed[] = [
                    'title' => str_replace(' - Upwork',
                        '',
                        $this->sanitize($node->getElementsByTagName('title')->item(0)->nodeValue)),
                    'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                    'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                    'description' => $description,
                    'country' => $country,
                    'apply_link' => $applyLink
                ];
                $feed->jobs()->create(['hash' => $hash]);
            }
            $this->sendUpdates($feed);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->notifyAdmins();
        }

    }

    /**
     * Remove all telegram unsupported stuff
     *
     * @param string $text
     * @return string
     */
    protected function sanitize(string $text)
    {
        $result = str_replace('<br />',"\n", $text);
        $result = str_replace('&nbsp;'," ", $result);
        $result = str_replace('&amp;',"&", $result);
        $result = str_replace('&rsquo;',"'", $result);
        $result = str_replace('&ldquo;','"', $result);
        $result = str_replace('&rdquo;','"', $result);
        $result = preg_replace('/<a .*>click to apply<\/a>/','',$result);
        return $result;
    }

    /**
     * Send new jobs to specified chat
     *
     * @param Feed $feed
     */
    protected function sendUpdates( Feed $feed )
    {
        $chat = $feed->chat;
        $user = $feed->user;
        $user->chat_id = $chat->chat_id;
        $userFilters = $user->filters;
        if ($userFilters) {
            $this->applyUserFilters($userFilters);
        }
        foreach ($this->feed as $item) {
            try {
                $user->notify(new JobNotification($feed->title, $item));
            } catch (\Exception $e) {
                Log::error('TLG_ERROR: '.$e->getMessage());
                Log::error('TLG_MESSAGE: '.$item['title'].' TTT '.$item['description']);
                $this->notifyAdmins();
            }
        }
        $feed->update(['dispatched_at' => Carbon::now()]);
    }

    /**
     * Apply user filters of all types
     *
     * @param $userFilters Collection of UserFilters
     */
    protected function applyUserFilters($userFilters)
    {
        foreach ($userFilters as $userFilter) {
            $methodName = 'apply'.$userFilter->type.'Filter';
            if (method_exists($this,$methodName)) {
                $this->$methodName($userFilter->value);
            }
        }
    }

    /**
     * Exclude jobs from selected countries
     *
     * @param string $value
     */
    protected function applyCountryFilter(string $value)
    {
        $countries = array_map('trim',explode(',',$value));
        foreach ($this->feed as $key=>$item) {
            if (in_array($item['country'],$countries)) {
                unset($this->feed[$key]);
            }
        }
    }

    /**
     * Notify admins via telegram if something gone wrong
     *
     */
    protected function notifyAdmins()
    {
        if (Setting::getSetting('debug') == 1) {
            $admins = User::isAdmin()->get();
            foreach ($admins as $admin) {
                $admin->chat_id = $admin->telegram_id;
                $admin->notify(new SimpleBotMessageNotification(trans('bot.debug_message')));
            }
        }
    }

    /**
     * Get Client country from job description
     *
     * @param string $description Full job description string
     * @return string Country name or 'Can't determine' string
     */
    protected function getCountry(string $description) : string
    {
        $matches = [];
        preg_match('/(<br \/><b>Country<\/b>:)(.*)(\n<br)/',$description,$matches);
        return count($matches) == 4 ? trim($matches[2]) : "Can't determine";
    }

    /**
     * Get job apply link from description
     *
     * @param string $description Full job description string
     * @return string Country name or 'https://www.upwork.com/jobs' string
     */
    protected function getApplyLink(string $description) : string
    {
        $result  = "https://www.upwork.com/jobs";
        preg_match('/(<br \/><a href=")(.*)(">click to apply<\/a>)/',$description,$matches);
        if (count($matches) == 4) {
            preg_match('/(.*)(%.*)(\?source=rss)/',trim($matches[2]),$matches);
        }
        if (count($matches) == 4) {
            $result = 'https://www.upwork.com/ab/proposals/job/'.$matches[2].'/apply/#/';
        }
        return $result;
    }
}
