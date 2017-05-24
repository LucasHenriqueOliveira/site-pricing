<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class PadSquad extends \App\Data\Scrape {

    private $isLoggedIn;

    public function __construct($params = []) {
        $this->isLoggedIn = false;
        $params['source_id'] = 20;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function login() {
        $res = $this->client()->request('POST', 'http://api.padsquad.com/login', [
            'cookies' => $this->jar(),
            'form_params' => [
                'username' => $this->username(),
                'password' => $this->password()
            ]
        ]);

        $data = json_decode($res->getBody()->getContents());
        if ($data->success) {
            $this->isLoggedIn = true;
            return true;
        }
        return false;
    }

    private function _extractPublisher($row) {
        if ($row && $row->_id && $row->_id->publisher) {
            $name = strtolower($row->_id->publisher);
            $publisher = $this->getPublisherFromName($name);
            if ($publisher && $publisher->publisher_id) {
                return $publisher->publisher_id;
            }
            return null;
        }
    }

    private function _extractGeo($row) {
        return 'us';
    }

    private function _extractAdSize($row) {
        if ($row && $row->_id && $row->_id->creative_size) {
            return $row->_id->creative_size;
        }
    }

    private function _extractDevice($row) {
        if ($row && $row->_id && $row->_id->device_type) {
            $name = strtolower($row->_id->device_type);
            if (in_array($name, ['tablet', 'phone'])) {
                return 'mob';
            }
            return 'dsk';
        }
    }

    private function _formatDateTime($date){
        return $date . ' 00:00:00';
    }

    public function import($params=[]){
        ini_set('max_execution_time', 180);

        $params['end'] = $params['start'];
        $source_id = $this->source_id();
        try {
            $report = $this->report([
                'start' => $params['start'],
                'end' => $params['end']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $source = $this->source();
        $product_type_id = $this->product_type_id();
        $startDate = $this->_formatDateTime($params['start']);

        foreach ($report as $row) {
            $arrData['date'] = $startDate;
            $arrData['publisher_id'] = $this->_extractPublisher($row);
            $arrData['device'] = $this->_extractDevice($row);
            $arrData['geo'] = $this->_extractGeo($row);
            $arrData['product_type_id'] = $product_type_id;
            $arrData['source_id'] = $source_id;
            $arrData['impressions'] = $row->{$source->impressions_field};
            $arrData['ad_size'] = $this->_extractAdSize($row);
            $arrData['page_views'] = '';
            $arrData['slot'] = '';
            try {
                $revenue_share = $this->getRevenueShare($arrData['publisher_id']);
                $arrData['net_revenue'] = $row->{$source->gross_revenue_field} * (float)$revenue_share->client_fraction;
                $arrData['gross_revenue'] = $row->{$source->gross_revenue_field};
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
    }

    public function report($params=[]) {
        $source_id = $this->source_id();
        if (!$this->isLoggedIn) {
            try {
                $res = $this->login();
            } catch (\Exception $e) {
                return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
            }
        }

        $start = str_replace('-', '', $params['start']);
        $end = str_replace('-', '', $params['end']);

        $res = $this->client()->request('GET', 'http://api.padsquad.com/report/ad/query/?group_by=creative_size&group_by=device_type&from='.$start.'&to='.$end.'&group_by=publisher', [
            'cookies' => $this->jar()
        ]);

        $data = json_decode($res->getBody()->getContents());
        if ($data->success) {
            return $data->data;
        }
    }
}
