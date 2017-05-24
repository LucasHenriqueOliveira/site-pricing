<?php

// note that this source does NOT use ssl, and is therefore sending usernames and passwords unencrypted over the net

namespace App\Data\Source;

use Log;
use App\Data\Source;

class MediaBong extends \App\Data\Scrape {

    public function __construct($params = []) {
        $params['source_id'] = 22;
        $params['product_type_id'] = 3; // Native
        parent::__construct($params);
    }

    public function login() {
        $res = $this->client()->request('POST', 'http://www.mediabong.net/controller/index/login', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
            'form_params' => [
                'login' => $this->username(),
                'password' => $this->password()
            ],
            /*
            'allow_redirects' => [
                'max'             => 5,
                'strict'          => false,
                'referer'         => true,
                'protocols'       => ['http', 'https'],
                'track_redirects' => true
            ]
            */
        ]);
        return $res->getBody();
    }

    public function group() {
        $res = $this->client()->request('GET', 'http://www.mediabong.net/publisher/reporting/group', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent(),
            ]
        ]);
        $html = $res->getBody()->getContents();

        preg_match_all('/var dataSet  = (\[\[\{.*\}\]\])/', $html, $matches);
        //print_r($matches[1][0]);
        if (!$matches[1][0]) {
            return null;
        }

        return json_decode($matches[1][0]);
    }

    public function ecpm($params = []) {
        // make sure we send it with slashes and not dashes
        $start = (new \DateTime($params['start']))->format('Y/m/d');
        $end = (new \DateTime($params['end']))->format('Y/m/d');
        $res = $this->client()->request('GET', 'http://www.mediabong.net/publisher/reporting/calculate_ecpm?scope=overview&date_range='.$start.'+-+'.$end.'&device=all&ad_format=SYNCROLL&geos=all&frequency=all', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent(),
            ]
        ]);
        $json = $res->getBody()->getContents();
        return json_decode($json);
    }

    public function token() {
        return $this->_token;
    }

}