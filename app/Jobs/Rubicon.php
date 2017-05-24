<?php

namespace App\Jobs;
use Log;

class Rubicon extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Rubicon start import for ".$this->_params['date']." to ".$this->_params['date']);
        $rubicon = new \App\Data\Source\Rubicon([
            'username' => env('RUBICON_USERNAME'),
            'password' => env('RUBICON_PASSWORD')
        ]);

        // @TODO - Note that the source_id is hard_coded here
        $res = $rubicon->import([
            'source_id' => 1,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("Rubicon end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Rubicon import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}