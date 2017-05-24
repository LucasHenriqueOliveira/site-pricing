<?php

namespace App\Jobs;
use Log;

class OpenXBidder extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("OpenXBidder start import of earliest email in inbox");
        $testVal = env('DO_CLEAR_INBOX') == 1;
        Log::info("OpenXBidder using value of doClearInbox: ".$testVal);
        $openx = new \App\Data\Source\OpenXBidder(['doClearInbox' => env('DO_CLEAR_INBOX')]);
        $res = $openx->import(['source_id' => 18]);
        Log::info("OpenXBidder end import of earliest email in inbox");
    }

    public function failed() {
        $msg = "OpenXBidder import from inbox failed";
        echo $msg;
        Log::info($msg);
    }
}