<?php

namespace App\Jobs;
use Log;

class Criteo extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Criteo start import for ".$this->_params['date']." to ".$this->_params['date']);
        $criteo = new \App\Data\Source\Criteo([
            'token' => $_ENV['CRITEO_TOKEN']
        ]);

        $res = $criteo->import([
            'source_id' => 13,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("Criteo end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Criteo import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}