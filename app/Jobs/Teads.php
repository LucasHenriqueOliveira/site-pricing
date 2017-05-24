<?php

namespace App\Jobs;
use Log;

class Teads extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Teads start import of earliest email in inbox");
        $testVal = env('DO_CLEAR_INBOX') == 1;
        Log::info("Teads using value of doClearInbox: ".$testVal);
        $teads = new \App\Data\Source\Teads(['doClearInbox' => env('DO_CLEAR_INBOX')]);
        $res = $teads->import(['source_id' => 14]);
        Log::info("Teads end import of earliest email in inbox");
    }

    public function failed() {
        $msg = "Teads import from inbox failed";
        echo $msg;
        Log::info($msg);
    }
}