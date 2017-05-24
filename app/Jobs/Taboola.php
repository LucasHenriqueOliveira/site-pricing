<?php

namespace App\Jobs;
use Log;

class Taboola extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Taboola start import for ".$this->_params['date']);
        $taboola = new \App\Data\Source\Taboola([
            'username' => env('TABOOLA_USERNAME'),
            'password' => env('TABOOLA_PASSWORD')
        ]);

        $res = $taboola->import([
            'source_id' => 5,
            'date' => $this->_params['date']
        ]);
        Log::info("Taboola end import for ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Taboola import failed for ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}