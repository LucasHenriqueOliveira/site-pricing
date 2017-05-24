<?php

namespace App\Jobs;
use Log;

class OpenX extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("OpenX start import of earliest email in inbox");
        $testVal = env('DO_CLEAR_INBOX') == 1;
        Log::info("OpenX using value of doClearInbox: ".$testVal);
        $openx = new \App\Data\Source\OpenX(['doClearInbox' => env('DO_CLEAR_INBOX')]);
        $res = $openx->import(['source_id' => 17]);
        Log::info("OpenX end import of earliest email in inbox");
    }

    public function failed() {
        $msg = "OpenX import from inbox failed";
        echo $msg;
        Log::info($msg);
    }
}