<?php

namespace App\Console\Commands;

use App\Feed;
use App\Notifications\JobNotification;
use Illuminate\Console\Command;

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
        if (!$feedId = $this->option('feed_id')) {
            return;
        }
        if (!$feed = Feed::find($feedId)) {
            return;
        }
        $rss = new \DOMDocument();
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
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                'description' => $description,
                'country' => $country,
                'apply_link' => $applyLink
            ];
            $feed->jobs()->create(['hash' => $hash]);
        }
        $this->sendUpdates($feed);
    }

    /**
     * Remove all telegram unsupported stuff
     *
     * @param string $text
     * @return string
     */
    protected function sanitize(string $text)
    {
        $result = str_replace('_','\\_',$text);
        $result = str_replace('*','\\*',$result);
        $result = str_replace('<br />',"\n", $result);
        $result = str_replace('<b>','*',$result);
        $result = str_replace('</b>','*',$result);
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
        foreach ($this->feed as $item) {
            $user->notify(new JobNotification($feed->title, $item));
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
