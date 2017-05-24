<?php

namespace App\Jobs;
use Log;

class PadSquad extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("PadSquad start import for ".$this->_params['date']." to ".$this->_params['date']);
        $PadSquad = new \App\Data\Source\PadSquad([
            'username' => env('PADSQUAD_USERNAME'),
            'password' => env('PADSQUAD_PASSWORD')
        ]);

        $res = $PadSquad->import([
            'source_id' => 20,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("PadSquad end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "PadSquad import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}