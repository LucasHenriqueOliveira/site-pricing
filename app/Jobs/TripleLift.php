<?php

namespace App\Jobs;
use Log;

class Triplelift extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("TripleLift start import for ".$this->_params['date']." to ".$this->_params['date']);
        $triplelift = new \App\Data\Source\TripleLift([
            'username' => env('TRIPLELIFT_USERNAME'),
            'password' => env('TRIPLELIFT_PASSWORD')
        ]);

        $res = $triplelift->import([
            'source_id' => 6,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("TripleLift end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "TripleLift import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}