<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Criteo extends \App\Data\Api {
    private $_token;

    public function __construct($params = []) {
        $this->_token = $params['token'];
        $params['source_id'] = 13;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function token() {
        return $this->_token;
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('Y-m-d');
    }

    public function report($params = []) {
        $res = $this->client()->request('GET', 'https://publishers.criteo.com/api/2.0/stats.json', [
            'query' => [
                'apitoken' => $this->token(),
                'begindate' => $this->formatDate($params['start']),
                'enddate' => $this->formatDate($params['end'])
            ]
        ]);

        return json_decode($res->getBody()->getContents());
    }

    public function import($params) {

        ini_set('max_execution_time', 180);

        $source_id = $this->source_id();
        $product_type_id = $this->product_type_id();
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

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $report = json_decode(json_encode($report),true);

        //metrics
        foreach ($report as $item) {
            $publisher = explode("-", $item['siteName']);
            $publisher_id = (int) $publisher[0];

            if($publisher_id) {
                $device = $this->getDevice($item['placementName']);
                $ad_size = $this->getAdSize($item['placementName']);

                $arrData['date'] = $item['date'];
                $arrData['publisher_id'] = $publisher_id;
                $arrData['device'] = $device;
                $arrData['ad_size'] = $ad_size;
                $arrData['product_type_id'] = $product_type_id;
                $arrData['source_id'] = $source_id;
                $arrData['impressions'] = $item[$source->impressions_field];
                $arrData['slot'] = '';
                $arrData['geo'] = '';
                $arrData['page_views'] = '';


                try {
                    // search client_fraction in revenue_share table
                    $revenue_share = $this->getRevenueShare($arrData['publisher_id']);
                    $arrData['net_revenue'] = $item[$source->gross_revenue_field]['value'] * (float)$revenue_share->client_fraction;
                    $arrData['gross_revenue'] = $item[$source->gross_revenue_field]['value'];
                } catch (\Exception $e) {
                    $this->setSourceStatus($source_id, 'Server Error', 6, $e->getMessage());
                }

                try {
                    $this->setBySourceFullSplit($arrData);
                } catch (\Exception $e) {
                    $this->setSourceStatus($source_id, 'Server Error', 9, $e->getMessage());
                }
            } else {
                $this->setSourceStatus($source_id, 'Server Error', 6, $item['placementName']);
            }
        }
        $this->refreshMetrics($params);
        return true;
    }

    public function getDevice($placementName) {
        $placementName = explode("-", $placementName);

        foreach ($placementName as $item) {
            if($item == 'mob') {
                return 'mob';
            }
        }
        return 'dsk';
    }

    public function getAdSize($placementName) {
        $placementName = explode("-", $placementName);
        return $placementName[count($placementName) - 1];
    }
}