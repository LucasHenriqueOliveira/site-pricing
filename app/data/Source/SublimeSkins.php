<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class SublimeSkins extends \App\Data\Api {
    private $_apiKey;
    private $_apiSecret;
    private $_importedZones = '{"zones":[
        {"zone_id": 2034, "zone": "technobuffalo.com", "publisher_id": 1, "geo": "in"},
        {"zone_id": 2152, "zone": "androidauthority.com", "publisher_id": 2, "geo": "in"},
        {"zone_id": 2153, "zone": "maxim.com", "publisher_id": 1, "geo": "in"},
        {"zone_id": 2962, "zone": "DupontRegistry_blog", "publisher_id": null, "geo": "in"},
        {"zone_id": 2963, "zone": "dupontregistry.com.autos", "publisher_id": 10, "geo": "in"},
        {"zone_id": 2964, "zone": "dupontregistry.com.homes", "publisher_id": 12, "geo": "in"},
        {"zone_id": 2965, "zone": "dupontregistry.com.boats", "publisher_id": 11, "geo": "in"},
        {"zone_id": 2966, "zone": "gottabemobile.com", "publisher_id": 20, "geo": "in"},
        {"zone_id": 2972, "zone": "veria.com", "publisher_id": 17, "geo": "in"},
        {"zone_id": 2973, "zone": "confitdent.com", "publisher_id": 7, "geo": "in"},
        {"zone_id": 4230, "zone": "technobuffalo.com", "publisher_id": 1, "geo": "us"},
        {"zone_id": 4235, "zone": "androidauthority.com", "publisher_id": 2, "geo": "us"},
        {"zone_id": 4236, "zone": "gottabemobile.com", "publisher_id": 20, "geo": "us"},
        {"zone_id": 4411, "zone": "mixedmartialarts.com", "publisher_id": 101, "geo": "us"},
        {"zone_id": 6063, "zone": "askdrmanny.com", "publisher_id": 3, "geo": "in"},
        {"zone_id": 6064, "zone": "youngmarriedchic.com", "publisher_id": 18, "geo": "in"},
        {"zone_id": 6065, "zone": "nextshark.com", "publisher_id": 25, "geo": "in"},
        {"zone_id": 6066, "zone": "bjpenn.com", "publisher_id": 27, "geo": "in"},
        {"zone_id": 6067, "zone": "tabtimes.com", "publisher_id": 29, "geo": "in"},
        {"zone_id": 6068, "zone": "soundguys.com", "publisher_id": 30, "geo": "in"},
        {"zone_id": 6073, "zone": "dupontregistry.com.autos", "publisher_id": 10, "geo": "us"},
        {"zone_id": 6074, "zone": "dupontregistry.com.homes", "publisher_id": 12, "geo": "us"},
        {"zone_id": 6075, "zone": "dupontregistry.com.boats", "publisher_id": 11, "geo": "us"},
        {"zone_id": 6076, "zone": "DupontRegistry_FullSite", "publisher_id": null, "geo": "us"},
        {"zone_id": 6078, "zone": "askdrmanny.com", "publisher_id": 3, "geo": "us"},
        {"zone_id": 6079, "zone": "youngmarriedchic.com", "publisher_id": 18, "geo": "us"},
        {"zone_id": 6080, "zone": "nextshark.com", "publisher_id": 25, "geo": "us"},
        {"zone_id": 6081, "zone": "bjpenn.com", "publisher_id": 27, "geo": "us"},
        {"zone_id": 6082, "zone": "tabtimes.com", "publisher_id": 29, "geo": "us"},
        {"zone_id": 6083, "zone": "soundguys.com", "publisher_id": 30, "geo": "us"},
        {"zone_id": 6084, "zone": "universalfreepress.com", "publisher_id": 38, "geo": "us"},
        {"zone_id": 6085, "zone": "brightscope.com", "publisher_id": 43, "geo": "us"},
        {"zone_id": 6098, "zone": "brightscope.com", "publisher_id": 43, "geo": "in"},
        {"zone_id": 6099, "zone": "universalfreepress.com", "publisher_id": 38, "geo": "in"},
        {"zone_id": 7555, "zone": "theactivetimes.com", "publisher_id": 59, "geo": "in"},
        {"zone_id": 9316, "zone": "carthrottle.com", "publisher_id": 67, "geo": "us"},
        {"zone_id": 9317, "zone": "dailydetroit.com", "publisher_id": 73, "geo": "us"},
        {"zone_id": 9318, "zone": "ectnews.com", "publisher_id": 68, "geo": "us"},
        {"zone_id": 9319, "zone": "ecommercetimes.com", "publisher_id": 69, "geo": "us"},
        {"zone_id": 9320, "zone": "technewsworld.com", "publisher_id": 70, "geo": "us"},
        {"zone_id": 9321, "zone": "crmbuyer.com", "publisher_id": 71, "geo": "us"},
        {"zone_id": 9322, "zone": "linuxinsider.com", "publisher_id": 72, "geo": "us"},
        {"zone_id": 14211, "zone": "brightscope.com", "publisher_id": 43, "geo": "us"},
        {"zone_id": 14212, "zone": "[US] PracticallyViral_FullSite", "publisher_id": null, "geo": "us"},
        {"zone_id": 14213, "zone": "wineonthestreet.com", "publisher_id": 49, "geo": "us"},
        {"zone_id": 14214, "zone": "theactivetimes.com", "publisher_id": 59, "geo": "us"},
        {"zone_id": 14215, "zone": "thedailymeal.com", "publisher_id": 61, "geo": "us"},
        {"zone_id": 14216, "zone": "ecoustics.com", "publisher_id": 76, "geo": "us"},
        {"zone_id": 14217, "zone": "[US] Unix_FullSite", "publisher_id": null, "geo": "us"},
        {"zone_id": 14218, "zone": "mysuburbankitchen.com", "publisher_id": 86, "geo": "us"},
        {"zone_id": 14219, "zone": "[US] CtrlQ_FullSite", "publisher_id": null, "geo": "us"},
        {"zone_id": 14220, "zone": "butterwithasideofbread.com", "publisher_id": 102, "geo": "us"},
        {"zone_id": 14221, "zone": "littleredwindow.com", "publisher_id": 100, "geo": "us"},
        {"zone_id": 14222, "zone": "bestfights.tv", "publisher_id": 129, "geo": "us"},
        {"zone_id": 14223, "zone": "the-line-up.com", "publisher_id": 131, "geo": "us"},
        {"zone_id": 14224, "zone": "thetab.com", "publisher_id": 133, "geo": "us"},
        {"zone_id": 14225, "zone": "minds.com", "publisher_id": 134, "geo": "us"},
        {"zone_id": 14226, "zone": "tyrereviews.co.uk", "publisher_id": 138, "geo": "us"},
        {"zone_id": 14227, "zone": "coolfords.com", "publisher_id": 135, "geo": "us"},
        {"zone_id": 14228, "zone": "chevytv.com", "publisher_id": 136, "geo": "us"},
        {"zone_id": 14229, "zone": "vettetv.com", "publisher_id": 137, "geo": "us"},
        {"zone_id": 15617, "zone": "thetab.com", "publisher_id": 133, "geo": "in"},
        {"zone_id": 16187, "zone": "mixedmartialarts.com", "publisher_id": 101, "geo": "in"}]}';

    public function __construct($params = []) {

        $this->_apiKey = $params['apiKey'];
        $this->_apiSecret = $params['apiSecret'];
        $params['source_id'] = 9;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function apiKey() {
        return $this->_apiKey;
    }

    public function apiSecret() {
        return $this->_apiSecret;
    }

    public function importZones() {
        return $this->_importedZones;
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('Y-m-d');
    }

    private function formatDateTime($date) {
        return $date. ' 00:00:00';
    }

    public function report($params) {

        $encodedApiKey = base64_encode($this->apiKey());
        $timestamp = time();
        $encodedHash = base64_encode(password_hash($timestamp . $this->apiSecret(), PASSWORD_BCRYPT));

        $res = $this->client()->request('GET', 'http://sasapi.ayads.co/stats/'.$this->formatDate($params['start']), [
            'query' => [
                'api-key' => $encodedApiKey,
                'timestamp' => $timestamp,
                'hash' => $encodedHash
            ]
        ]);

        return json_decode($res->getBody()->getContents());
    }

    public function import($params) {
        ini_set('max_execution_time', 180);
        $source_id = $this->source_id();
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

        $report = json_decode(json_encode($report),true);

        foreach ($report as $row) {
            $data = $this->getZone($row);

            $arrData['date'] = $this->formatDateTime($params['start']);
            $arrData['publisher_id'] = $data['publisher_id'];
            $arrData['geo'] = $data['geo'];
            $arrData['product_type_id'] = $product_type_id;
            $arrData['source_id'] = $source_id;
            $arrData['impressions'] = $row[$source->impressions_field];
            $arrData['device'] = '';
            $arrData['slot'] = '';
            $arrData['ad_size'] = '';
            $arrData['page_views'] = '';

            try {
                // search client_fraction in revenue_share table
                $revenue_share = $this->getRevenueShare($arrData['publisher_id']);
                $arrData['net_revenue'] = $row[$source->gross_revenue_field] * (float)$revenue_share->client_fraction;
                $arrData['gross_revenue'] = $row[$source->gross_revenue_field];
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

    public function getZone($row) {
        $data = json_decode($this->importZones(), true)['zones'];

        foreach ($data as $item) {
            if($item['zone_id'] == $row['zone']) {
                return $item;
            }
        }
        return false;
    }
}