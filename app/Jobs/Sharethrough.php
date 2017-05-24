<?php

namespace App\Jobs;
use Log;

class Sharethrough extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Sharethrough start import of earliest email in inbox");
        $testVal = env('DO_CLEAR_INBOX') == 1;
        Log::info("Sharethrough using value of doClearInbox: ".$testVal);
        $st = new \App\Data\Source\Sharethrough(['doClearInbox' => env('DO_CLEAR_INBOX')]);
        $res = $st->import(['source_id' => 21]);
        Log::info("Sharethrough end import of earliest email in inbox");
    }

    public function failed() {
        $msg = "Sharethrough import from inbox failed";
        echo $msg;
        Log::info($msg);
    }
}