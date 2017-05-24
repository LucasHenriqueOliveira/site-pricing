<?php

namespace App\Data;

class Scrape extends Source {
    private $_jar;
    private $_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';

    public function __construct($params = []) {
        $this->_jar = new \GuzzleHttp\Cookie\CookieJar;
        parent::__construct($params);
    }

    public function login() {

    }

    public function jar($jar = null) {
        if (!is_null($jar)) {
            $this->_jar = $jar;
        }
        return $this->_jar;
    }

    public function agent() {
        return $this->_agent;
    }
}