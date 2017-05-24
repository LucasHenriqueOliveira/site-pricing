<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Log;

class QueueJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queueadd {--set=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $set = $this->option('set');
        Log::info("Queue Jobs::Handle with set: ".$set);
        // We want to run for yesterday
        $date = (new \DateTime())->modify('-1 day')->format('Y-m-d');
        $params = [
            'date' => $date,
        ];

        if ($set === "early" || $set === "all") {
            dispatch(new \App\Jobs\Rubicon($params));
            dispatch(new \App\Jobs\Sovrn($params));
            dispatch(new \App\Jobs\Taboola($params));
            dispatch(new \App\Jobs\Connatix($params));
            dispatch(new \App\Jobs\Teads($params));
        }
        if ($set === "later" || $set === "all") {
            dispatch(new \App\Jobs\OpenX($params));
            dispatch(new \App\Jobs\OpenXBidder($params));
            dispatch(new \App\Jobs\Sharethrough($params));
            dispatch(new \App\Jobs\Aol($params));
        }
//        dispatch(new \App\Jobs\TripleLift($params));
//        dispatch(new \App\Jobs\AdYouLike($params));
//        dispatch(new \App\Jobs\SublimeSkins($params));
//        dispatch(new \App\Jobs\Unruly($params));
//        dispatch(new \App\Jobs\Realvu($params));
//        dispatch(new \App\Jobs\Aol($params));

        $this->info('Jobs added to queue');
    }
}