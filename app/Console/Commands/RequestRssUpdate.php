<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RequestRssUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request RSS update from Upwork';

    /**
     * Upwork RSS feed url
     *
     * @var string
     */
    protected $url = '';

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
        $this->url = 'https://www.upwork.com/ab/feed/jobs/rss?budget=100-499%2C500-999%2C1000-4999%2C5000-&contractor_tier=2%2C3&verified_payment_only=1&proposals=0-4%2C5-9%2C10-14%2C15-19&q=laravel&sort=recency&paging=0%3B50&api_params=1&securityToken=45f8d8b652bb757ec5d3cb384f649a9592f42a54e127e28c323daa79b648b84499a03cfe7ab5c2c3e8265219f23e149e9eb51aaa0040b32d320c9274b7179326&userUid=736143449467912192&orgUid=736143481616330753';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rss = new \DOMDocument();
        $rss->load($this->url);
        foreach ($rss->getElementsByTagName('item') as $node) {
            $description = $node->getElementsByTagName('description')->item(0)->nodeValue;
            $this->feed[] = [
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                'description' => $description,
                'country' => $this->getCountry($description),
                'apply_link' => $this->getApplyLink($description)
            ];
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
        preg_match('/(<br \/><a href=")(.*)(">click to apply<\/a>)/',$description,$matches);
        return count($matches) == 4 ? trim($matches[2]) : "https://www.upwork.com/jobs";
    }
}
