<?php

// note that the documenation at https://wiki.appnexus.com/display/sdk/Publisher+Analytics+Report is wrong in a few ways

namespace App\Data\Source;

class AppNexus extends \App\Data\Scrape {
    private $_token;
    private $_uuid;

    public function __construct($params = []) {
        $params['source_id'] = 19;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function login() {
        $res = $this->client()->request('POST', 'https://api.appnexus.com/auth', [
            'cookies' => $this->jar(),
            'json' => [
                'auth' => [
                    'username' => $this->username(),
                    'password' => $this->password()
                ]
            ]
        ]);
        $data = json_decode($res->getBody());
        if (!$data->response->token) {
            return false;
        }
        $this->_token = $data->response->token;
        $this->_uuid = $data->response->dbg_info->uuid;

        return true;
    }

    public function report($params) {
        $res = $this->client()->request('POST', 'http://api.appnexus.com/report', [
            'cookies' => $this->jar(),
            'json' => [
               'report' => [
                   'report_type' => "network_analytics",
                   'format' => 'csv',
                   'start_date' => '2017-03-01 00:00:00',
                   'end_date' => '2017-03-02 00:00:00',
                   'columns' => [
                       "day",
                       "imps",
                       "clicks",
                       "revenue",
                       "ctr",
                       "placement_name",
                       "size",
                       "geo_country",
                       "media_type"
                   ]
               ]
           ]
        ]);
        $data = json_decode($res->getBody());
        $id = $data->response->report_id;

        for ($i=0; $i <= 20; $i++) {

            $res = $this->client()->request('GET', 'http://api.appnexus.com/report?id=' . $id, [
                'cookies' => $this->jar()
            ]);
            print_r(json_decode($res->getBody()));die;
            $data = json_decode($res->getBody());
            if ($data->response->execution_status == 'ready') {
                break;
            }

            if ($i == 20) {
                return false;
            }
            sleep(1);
        }

        $res = $this->client()->request('GET', 'http://api.appnexus.com/report-download?id=' . $id, [
            'cookies' => $this->jar()
        ]);
        $data = json_decode($res->getBody());

        return $data;
    }

    public function token() {
        return $this->_token;
    }

    public function uuid() {
        return $this->_uuid;
    }
}