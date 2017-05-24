<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class TripleLift extends \App\Data\Api {
    private $_accessToken;

    public function __construct($params = []) {
        $params['source_id'] = 6;
        $params['product_type_id'] = 3; // Native
        parent::__construct($params);
    }

    public function login() {
        $res = $this->client()->request('POST', 'http://api.triplelift.com/login', [
            'json' => [
                'username' => $this->username(),
                'password' => $this->password()
            ]
        ]);

        $data = json_decode($res->getBody()->getContents());
        if (!$data->token) {
            return false;
        }
        $this->_accessToken = $data->token;

        return true;
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('Y-m-d H:i:s');
    }

    private function formatDateTime($date) {
        return $date. ' 00:00:00';
    }

    public function report($params) {

        $res = $this->client()->request('POST', 'https://api.triplelift.com/reporting/v2/pub_side/legacy', [
            'body' => '{"dimensions":[
                "ymd","publisher_id","publisher_name","domain","placement_id","placement_name","country_name","device_type"],
                "metrics":[
                    "rendered","publisher_revenue"
                ],
                "filters":{},
                "end_date":"'.$this->formatDate($params['end']).'",
                "start_date":"'.$this->formatDate($params['start']).'",
                "report_for":"publisher",
                "sort":{},
                "publisher_id": 575}',
            'headers' => [
                'Auth-token' => $this->accessToken(),
                'Content-Type' => 'application/json'
            ]
        ]);
        $data = json_decode($res->getBody()->getContents());
        return $data;
    }

    public function import($params) {
        ini_set('max_execution_time', 180);

        $source_id = $this->source_id();

        try {
            $res = $this->login();
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
        }

        try {
            $report = $this->report([
                'start' => $params['start'],
                'end' => $params['end']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }

        $source = $this->source();
        $product_type_id = $this->product_type_id();

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $report = json_decode(json_encode($report->report),true);

        foreach ($report as $row) {

            $arrData['date'] = $this->formatDateTime($row['ymd']);
            $arrData['publisher_id'] = $this->extractPublisher($row);
            $arrData['device'] = $this->extractDevice($row[$source->device_field]);
            $arrData['geo'] = $this->extractGeo($row[$source->geo_field]);
            $arrData['product_type_id'] = $product_type_id;
            $arrData['source_id'] = $source_id;
            $arrData['impressions'] = $row[$source->impressions_field];
            $arrData['ad_size'] = '';
            $arrData['page_views'] = '';
            $arrData['slot'] = '';

            try {
                // search client_fraction in revenue_share table
                $revenue_share = $this->getRevenueShare($arrData['publisher_id']);
                $arrData['gross_revenue'] = $row[$source->gross_revenue_field];
                $arrData['net_revenue'] = $row[$source->gross_revenue_field] * (float)$revenue_share->client_fraction;
            } catch (\Exception $e) {
                $this->setSourceStatus($source_id, 'Server Error', 6, $e->getMessage());
            }

            try {
                $this->setBySourceFullSplit($arrData);
            } catch (\Exception $e) {
                $this->setSourceStatus($source_id, 'Server Error', 9, $e->getMessage());
            }
        }

        $this->refreshMetrics($params);
        return true;
    }

    public function extractPublisher($import) {
        if ($import) {
            if($import['domain'] == 'Other') {
                $domain = explode('_', $import["placement_name"]);
                $name = $domain[0];
            } else {
                $name = $import["domain"];
            }

            $publisher = $this->getPublisherFromName($name);

            return $publisher->publisher_id;
        }
    }

    public function extractDevice($device) {
        $device = strtolower($device);

        if ($device === "desktop") {
            $retval = "dsk";
        } else {
            $retval = "mob";
        }
        return $retval;
    }

    public function extractGeo($geo) {
        $geo = strtolower($geo);

        if ($geo === "united states") {
            $retval = "us";
        } else if ($geo === "unknown") {
            $retval = "n/a";
        } else {
            $retval = "in";
        }
        return $retval;
    }

    public function accessToken() {
        return $this->_accessToken;
    }
}