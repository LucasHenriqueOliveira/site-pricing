<?php

namespace App\Jobs;
use Log;

class Sovrn extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Sovrn start import for ".$this->_params['date']." to ".$this->_params['date']);
        $sovrn = new \App\Data\Source\Sovrn([
            'username' => env('SOVRN_USERNAME'),
            'password' => env('SOVRN_PASSWORD')
        ]);

        $res = $sovrn->import([
            'source_id' => 2,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("Sovrn end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Sovrn import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}