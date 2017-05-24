<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ImportIntoDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import {--source=} {--date=}';

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
        $source_id = $this->option('source');
        $date = $this->option('date');

        $params = [
            'date' => $date
        ];

        // @TODO - Note that this is hardcoded here and within the job itself - we should get rid of this if we can
        switch ($source_id) {
            case 1:
            case 'rubicon':
                $job = new \App\Jobs\Rubicon($params);
                break;
            case 2:
            case 'sovrn':
                $job = new \App\Jobs\Sovrn($params);
                break;
            case 3:
            case 'connatix':
                $job = new \App\Jobs\Connatix($params);
                break;
            case 4:
            case 'technorati':
                $job = new \App\Jobs\Technorati($params);
                break;
            case 5:
            case 'taboola':
                $job = new \App\Jobs\Taboola($params);
                break;
            case 6:
            case 'triplelift':
                $job = new \App\Jobs\TripleLift($params);
                break;
            case 7:
            case 'adyoulike':
                $job = new \App\Jobs\AdYouLike($params);
                break;
            case 9:
            case 'sublimeskins':
                $job = new \App\Jobs\SublimeSkins($params);
                break;
            case 10:
            case 'unruly':
                $job = new \App\Jobs\Unruly($params);
                break;
            case 11:
            case 'teads':
                $job = new \App\Jobs\Teads($params);
                break;
            case 12:
            case 'realvu':
                $job = new \App\Jobs\Realvu($params);
                break;
            case 13:
            case 'criteo':
                $job = new \App\Jobs\Criteo($params);
                break;
            case 14:
            case 'aol':
                $job = new \App\Jobs\Aol($params);
                break;
            case 17:
            case 'openx':
                $job = new \App\Jobs\OpenX($params);
                break;
            case 18:
            case 'openxbidder':
                $job = new \App\Jobs\OpenXBidder($params);
                break;
            case 21:
            case 'sharethrough':
                $job = new \App\Jobs\Sharethrough($params);
                break;
            case 23:
            case 'teads':
                $job = new \App\Jobs\Teads($params);
                break;
            case 'email':
                $job = new \App\Jobs\Email($params);
                break;
        }

        dispatch($job);

        $this->info('Source import complete for source_id '.$source_id. ' and date '.$date);
    }
}