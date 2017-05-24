<?php

namespace App\Jobs;
use Log;

class Aol extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("AOL start import of earliest email in inbox");
        $testVal = env('DO_CLEAR_INBOX') == 1;
        Log::info("AOL using value of doClearInbox: ".$testVal);
        $aol = new \App\Data\Source\Aol(['doClearInbox' => env('DO_CLEAR_INBOX')]);
        $res = $aol->import(['source_id' => 14]);
        Log::info("AOL end import of earliest email in inbox");
    }

    public function failed() {
        $msg = "AOL import from inbox failed";
        echo $msg;
        Log::info($msg);
    }
}