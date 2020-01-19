<?php

namespace App\Console\Commands;

use App\Feed;
use App\Job;
use App\Jobs\RequestUpdateJob;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DispatchRssJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all feeds with specific delays';

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
        $delay = 0;
        if ($delaySet = Setting::getSetting('feed_delay')) {
            $delay = (int) $delaySet;
        }
        $feeds = Feed::whereHas('user',function($query) {
            $query->where('is_active','=',1)
                ->where('is_banned','=',0);
        })->get();
        $activeUpdates = Job::ofQueue('feedUpdate')->count();
        $updateDelay = $delay*$activeUpdates;
        foreach ($feeds as $feed) {
            if (!$feed->dispatched_at || Carbon::parse($feed->dispatched_at) <= Carbon::now()->subMinutes($feed->interval)) {
                dispatch(new RequestUpdateJob($feed->id))->onQueue('feedUpdate')->delay(now()->addSeconds($updateDelay));
            }
            $updateDelay += $delay;
        }
    }
}
