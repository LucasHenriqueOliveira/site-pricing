<?php

namespace App\Jobs;
use Log;

class Technorati extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Technorati start import for ".$this->_params['date']." to ".$this->_params['date']);
        $technorati = new \App\Data\Source\Technorati([
            'username' => env('TECHNORATI_USERNAME'),
            'password' => env('TECHNORATI_PASSWORD')
        ]);

        $res = $technorati->import([
            'source_id' => 4,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("Technorati end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Technorati import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}