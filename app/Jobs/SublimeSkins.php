<?php

namespace App\Jobs;
use Log;

class SublimeSkins extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("SublimeSkinz start import for ".$this->_params['date']." to ".$this->_params['date']);
        $sublimeskins = new \App\Data\Source\SublimeSkins([
            'apiKey' => $_ENV['SUBLIMESKINZ_KEY'],
            'apiSecret' => $_ENV['SUBLIMESKINZ_SECRET']
        ]);

        $res = $sublimeskins->import([
            'source_id' => 9,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("SublimeSkinz end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "SublimeSkinz import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}