<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Realvu extends \App\Data\Scrape {

    public function __construct($params = []) {
        $params['source_id'] = 11;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }


    public function login() {
        $res = $this->client()->request('POST', 'https://control.realvu.net/office/Login.aspx', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
            'form_params' => [
                'userid' => $this->username(),
                'password' => $this->password(),
                'submit' => 'Log in'
            ]
        ]);

        // failed to login
        if (!$res->getHeader('X-Guzzle-Redirect-History')) {
            return false;
        }

        return true;
    }

    private function formatDateTime($date) {
        return $date. ' 00:00:00';
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('m/d/Y');
    }

    public function report($params = []) {

        $res = $this->client()->request('POST', 'https://control.realvu.net/net/daily_revenue_report.aspx', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
            'form_params' => [
                'task' => '',
                'ord' => '',
                'w' => 0,
                'q' => '',
                'showZeros' => 'on',
                'campFrom' => $this->formatDate($params['start']),
                'campTo' => $this->formatDate($params['start']),
                'dateSel' => 'set',
                'tSpan' => 'set'
            ]
        ]);

        $res = $this->client()->request('GET', 'https://control.realvu.net/net/daily_revenue_report.aspx?format=csv', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ]
        ]);

        $data = $this->csvToAssoc($res->getBody()->getContents(), 0 , false, "\r");

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
                'start' => $params['start']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }

        $source = $this->source();
        $product_type_id = $this->product_type_id();

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        //metrics
        foreach ($report as $item) {
            if($item['id']) {
                $arrName = explode(' ', strtolower($item['name']));
                $ad_size = $arrName[count($arrName) - 1];

                // remove ad_size
                unset($arrName[count($arrName) - 1]);

                // remove rotating
                unset($arrName[count($arrName) - 1]);

                $strPublisher = '';
                foreach ($arrName as $searchPublisher) {
                    $strPublisher .= $searchPublisher. '%';
                }

                $publisher = $this->getPublisherFromName($strPublisher);

                if($publisher) {

                    $arrData['date'] = $this->formatDateTime($params['start']);
                    $arrData['publisher_id'] = $publisher->publisher_id;
                    $arrData['product_type_id'] = $product_type_id;
                    $arrData['source_id'] = $source_id;
                    $arrData['impressions'] = $item[$source->impressions_field];
                    $arrData['ad_size'] = $ad_size;
                    $arrData['geo'] = '';
                    $arrData['device'] = '';
                    $arrData['slot'] = '';
                    $arrData['page_views'] = '';

                    try {
                        // search client_fraction in revenue_share table
                        $revenue_share = $this->getRevenueShare($arrData['publisher_id']);
                        $arrData['net_revenue'] = $item[$source->gross_revenue_field] * (float)$revenue_share->client_fraction;
                        $arrData['gross_revenue'] = $item[$source->gross_revenue_field];
                    } catch (\Exception $e) {
                        $this->setSourceStatus($source_id, 'Server Error', 6, $e->getMessage());
                    }

                    try {
                        $this->setBySourceFullSplit($arrData);
                    } catch (\Exception $e) {
                        $this->setSourceStatus($source_id, 'Server Error', 9, $e->getMessage());
                    }
                } else {
                    $this->setSourceStatus($source_id, 'Server Error', 6, strtolower($item['name']));
                }
            }
        }
        $this->refreshMetrics($params);
        return true;
    }
}