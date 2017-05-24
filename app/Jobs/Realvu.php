<?php

namespace App\Jobs;
use Log;

class Realvu extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("RealVu start import for ".$this->_params['date']." to ".$this->_params['date']);
        $realvu = new \App\Data\Source\Realvu([
            'username' => $_ENV['REALVU_USERNAME'],
            'password' => $_ENV['REALVU_PASSWORD']
        ]);

        $res = $realvu->import([
            'source_id' => 12,
            'start' => $this->_params['date'],
            'end' => $this->_params['date']
        ]);
        Log::info("RealVu start import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "RealVu import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}