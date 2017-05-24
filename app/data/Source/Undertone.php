<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Undertone extends \App\Data\Scrape {
    public function login() {
        $res = $this->client()->request('POST', 'https://insights.undertone.com/user/process_login', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
            'form_params' => [
                'user' => [
                    'email' => $this->username(),
                    'password' => $this->password()
                ]
            ],
            'allow_redirects' => [
                'referer'         => true,
                'track_redirects' => true
            ]
        ]);

        // failed to login
        if (!$res->getHeader('X-Guzzle-Redirect-History')) {
            return false;
        }

        return true;
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('m/d/Y');
    }

    public function orders($params = []) {
        $params['type'] = 'po_details';
        return $this->report($params);
    }

    public function zones($params = []) {
        $params['type'] = 'custom_report_113';
        return $this->report($params);
    }

    public function report($params = []) {

        $res = $this->client()->request('POST', 'https://insights.undertone.com/report/submit_report', [
            'cookies' => $this->jar(),
            'headers' => [
                'Referer' => 'https://insights.undertone.com/report/show',
                'User-Agent' => $this->agent()
            ],
            'form_params' => [
                '_method' => 'put',
                'date_range' => 'yesterday',
                'start' => $this->formatDate($params['start']),
                'end' => $this->formatDate($params['end']),
                'filter' => [
                    'currency' => [
                        'USD'
                    ],
                    'int_dom' => '',
                    'division' => [],
                    'po_type' => '',
                    'ad_unit' => [],
                    'player_type' => [],
                    'product' => [],
                    'product_category' => [],
                    'tracker' => [],
                    'campaign' => [],
                    'os' => [],
                    'browser' => [],
                    'device' => [],
                    'device_os' => [],
                    'release_date' => []
                ],
                'report' => [
                    'id' => $params['type'],
                    'type' => 'csv'
                ],
                'allow_redirects' => [
                    'referer'         => false,
                    'track_redirects' => true
                ]
            ]
        ]);

        $processData = function($res) {

            $raw = explode("\n", $res->getBody()->getContents());
            array_shift($raw);array_shift($raw);array_shift($raw);array_shift($raw);array_shift($raw);array_shift($raw);
            array_pop($raw);array_pop($raw);array_pop($raw);

            $data = $this->csvToAssoc(implode("\n", $raw));
            return $data;
        };

        // we are being redirected to a status page. need to check it later
        // @todo: this isnt working right. hard to reproduce the loading part
        if ($res->getHeader('Location')) {
            echo 'checking...';
            for ($i=0; $i < 5; $i++) {
                sleep(1);
                $status = $this->client()->request('GET', $res->getHeaders()['Location'].'.json', [
                    'cookies' => $this->jar()
                ]);
                print_r($status->getBody()->getContents());
                $status = json_encode($status->getBody()->getContents());


            }
            $loc = preg_match_all('/status\/(.*)/i', $res->getHeaders()['Location'], $matches);
            $data = $this->client()->request('GET', 'https://insights.undertone.com/report/show/'.$matches[1][0], [
                'cookies' => $this->jar()
            ]);

            print_r($data);
            exit;

            return $processData($data);
        }
        return $processData($res);
    }
}