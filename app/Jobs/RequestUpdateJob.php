<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class RequestUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Id of feed to update
     *
     * @var null
     */
    protected $feedId = null;

    /**
     * Create a new job instance.
     *
     * @param int $feedId
     */
    public function __construct(int $feedId)
    {
        $this->feedId = $feedId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('rss:update',['--feed_id' => $this->feedId]);
    }
}
