<?php

namespace App\Jobs;
use Log;

class Connatix extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Connatix start import for ".$this->_params['date']);
        $connatix = new \App\Data\Source\Connatix([
            'username' => env('CONNATIX_USERNAME'),
            'password' => env('CONNATIX_PASSWORD')
        ]);

        $res = $connatix->import([
            'source_id' => 3,
            'date' => $this->_params['date'],
        ]);
        Log::info("Connatix end import for ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Connatix import failed for ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}