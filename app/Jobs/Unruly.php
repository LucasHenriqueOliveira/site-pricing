<?php

namespace App\Jobs;
use Log;

class Unruly extends Job {
    private $_params;

    public function __construct($params = []) {
        $this->_params = $params;
    }

    public function handle() {
        Log::info("Unruly start import for ".$this->_params['date']." to ".$this->_params['date']);
        $unruly = new \App\Data\Source\Unruly([
            'username' => $_ENV['UNRULY_USERNAME'],
            'password' => $_ENV['UNRULY_PASSWORD']
        ]);

        $res = $unruly->import([
            'source_id' => 10,
            'start' => $this->_params['date'],
            'end' => $this->_params['date'],
            'device' => 'mob'
        ]);

        $res = $unruly->import([
            'source_id' => 10,
            'start' => $this->_params['date'],
            'end' => $this->_params['date'],
            'device' => 'dsk'
        ]);
        Log::info("Unruly end import for ".$this->_params['date']." to ".$this->_params['date']);
    }

    public function failed() {
        $msg = "Unruly import failed for ".$this->_params['date']." to ".$this->_params['date'];
        echo $msg;
        Log::info($msg);
    }
}