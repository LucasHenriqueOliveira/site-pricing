<?php

// note that the documenation at https://wiki.appnexus.com/display/sdk/Publisher+Analytics+Report is wrong in a few ways

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Connatix extends \App\Data\Scrape {

    private $_customLineItemRe1 = '/^([0-9]{4,6})-.*-(native-ad-[a-d])(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe2 = '/^(.*)-.*/i';

    public function __construct($params = []) {
        $params['source_id'] = 3;
        $params['product_type_id'] = 3;  // Native
        parent::__construct($params);
    }

    public function login() {

        try {
            $res = $this->client()->request('POST', 'https://console.connatix.com/api/account/Login', [
                'cookies' => $this->jar(),
                'json' => [
                    'Username' => $this->username(),
                    'Password' => $this->password()
                ],
                'headers' => [
                    'User-Agent' => $this->agent(),
                ]
            ]);
        } catch (\Exception $e) {
            // failed to log in
            return false;
        }

        $data = json_decode($res->getBody()->getContents());
        if (!$data->Success) {
            return false;
        }

        return true;
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('Y-m-d');
    }

    private function formatDateTime($date) {
        return $date. ' 00:00:00';
    }

    public function getDimensions($params){

        $res = $this->client()->request('POST', 'https://console-old.connatix.com/Analytics/GetDimension', [
            'cookies' => $this->jar(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Referer' => 'https://console-old.connatix.com/publisher/analytics',
                'User-Agent' => $this->agent()
            ],
            // @todo: convert to php array to edit easier
            'body' => '{"shareId":"",
                "type":"PublisherOverview",
                "count": 50000,
                "includeDeleted": false,
                "page": 1,
                "name": "'.$params['name'].'",
                "startDate":"'.$this->formatDate($params['start']).'",
                "endDate":"'.$this->formatDate($params['end']).'",
                "text": "",
                "userId":""}'
        ]);

        $data = json_decode($res->getBody()->getContents(), true);

        return $data;
    }

    public function download($params) {

        // Note that Connatix does not correctly handle the case where multiple dates are downloaded
        //  so we enforce a single date

        if ($params['start'] != $params['end']) {
            $params['end'] = $params['start'];
            Log::info("Warning: Connatix end date is set to start date: ".$params['start'] );
        }
        $res = $this->client()->request('POST', 'https://console-old.connatix.com/Analytics/Get', [
            'cookies' => $this->jar(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Referer' => 'https://console-old.connatix.com/publisher/analytics',
                'User-Agent' => $this->agent()
            ],
            // @todo: convert to php array to edit easier
            'body' => '{"shareId":"",
                "type":"PublisherOverview",
                "startDate":"'.$this->formatDate($params['start']).'",
                "endDate":"'.$this->formatDate($params['end']).'",
                "dimensionSelection":[
                    {"dimension":{"name":"Ads"},"selections":[],"selectionsAll":true,"showIDs":false},
                    {"dimension":{"name":"Devices"},"selections":[
                        {"ID":"0","Name":"Desktop","Active":true,"Appearances":0},
                        {"ID":"1","Name":"iPhone iOS < 9","Active":true,"Appearances":0},
                        {"ID":"2","Name":"Android","Active":true,"Appearances":0},
                        {"ID":"3","Name":"iPad iOS < 9","Active":true,"Appearances":0},
                        {"ID":"4","Name":"AndroidTablet","Active":true,"Appearances":0},
                        {"ID":"5","Name":"Unknown","Active":true,"Appearances":0},
                        {"ID":"6","Name":"iPhone iOS >= 10","Active":true,"Appearances":0},
                        {"ID":"7","Name":"iPad iOS >= 10","Active":true,"Appearances":0}
                    ],"selectionsAll":true,"showIDs":false},
                    {"dimension":{"name":"Countries"},"selections":[],"selectionsAll":true,"showIDs":false}],
                "networkTargeting":"Public",
                "userId":""}'
        ]);
        $data = json_decode($res->getBody()->getContents(), true);

        return $data;
    }

    public function searchForDimensions($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['ID'] === $id) {
                return $val['Name'];
            }
        }
        return null;
    }

    public function extractRow($import, $ads, $devices, $source, $date) {
        // If the row is blank, we don't want to output an error
        $extracted = null;
        if ($import) {
            $line_item = $this->searchForDimensions($import['DimensionData'][0]['Value'], $ads['Filters']);
            if ($line_item) {
                $parsed = $this->parseLineItem($line_item);
                if (!$parsed) {
                    // @TODO - Make the info more user friendly - maybe use the original line
                    if ($line_item) {
                        $msg = $line_item;
                    } else {
                        $msg = json_encode($import);
                    }
                    $extracted["error"] = ["code" => 7, "info" => $msg];
                    $extracted["line_item"] = $line_item;
                    return $extracted;
                } else {
                    if (!$parsed["publisher_id"] && $parsed["publisher_name"]) {
                        $publisher_id = $this->getPublisherIdFromPublisherName($parsed["publisher_name"]);
                        if ($publisher_id) {
                            $parsed["publisher_id"] = $publisher_id;
                        } else {
                            $extracted["error"] = ["code" => 6, "info" => $import["Site"]];
                            return $extracted;
                        }
                    } else if (!$parsed["publisher_id"]){
                        $extracted["error"] = ["code" => 7, "info" => json_encode($import)];
                        return $extracted;
                    }
                    $extracted = [];
                    $device = $this->searchForDimensions($import['DimensionData'][1]['Value'], $devices['Filters']);
                    $deviceLC = strtolower($device);
                    if($deviceLC == 'desktop') {
                        $device = 'dsk';
                    } else if ($deviceLC == 'unknown' || !deviceLC) {
                        $device = 'n/a';
                    } else {
                        $device = 'mob';
                    }

                    $country = $import['DimensionData'][2]['Value'];
                    if($country == 'US') {
                        $country = 'us';
                    } else {
                        $country = 'in';
                    }

                    $extracted['date'] = $this->formatDateTime($date);
                    $extracted['publisher_id'] = $parsed["publisher_id"];
                    $extracted['device'] = $device;
                    $extracted['geo'] = $country;
                    $extracted['slot'] = $this->chooseFromTwo($parsed["slot"], "n/a");
                    $extracted['impressions'] = Source::extractNumber($import[$source->impressions_field]);

                    try {
                        // search client_fraction in revenue_share table
                        $revenue_share = $this->getRevenueShare($extracted['publisher_id']);
                        $gross_revenue = Source::extractNumber($import[$source->gross_revenue_field]);
                        $extracted['gross_revenue'] = $gross_revenue;
                        $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;
                    } catch (\Exception $e) {
                        $extracted["error"] = ["code" => 6, "info" => $e->getMessage()];
                        return $extracted;
                    }
                }
            }
        }
        return $extracted;
    }

    public function processDownload($report, $ads, $devices, &$badPublisherIds, $params = []) {

        $source_id = $this->source_id();
        $source = $this->source();
        $product_type_id = $this->product_type_id();

        $toDb = [];
        foreach ($report as $item) {
            $extracted = $this->extractRow($item, $ads, $devices, $source, $params['start']);
            if(!is_null($extracted)){
                if (array_key_exists("error", $extracted)) {
                    $error = $extracted["error"];
                    $this->setSourceStatus($source_id, 'Server Error', $error["code"], $error["info"]);
                } else {
                    $extracted['source_id'] = $source_id;
                    $extracted['product_type_id'] = $product_type_id;
                    $toDb[] = $extracted;
                }
            }
        }

        $consolidated = $this->consolidateDataRows($toDb);
        $consolidated = $this->removeBadPublisherIds($consolidated, $badPublisherIds);
        return $consolidated;

    }

    public function parseLineItem($s) {
        $standardParse = $this->matchStandardNativeLineItem($s);
        if ($standardParse) {
            $parsed = $standardParse;
        } else {
            $parsed = $this->matchNonStandardLineItem($s);
        }
        return $parsed;
    }

    public function matchNonStandardLineItem($s) {
        $parsed = $this->matchNonStandardLineItem1($s);
        if ($parsed) {
            return $parsed;
        }
        $parsed = $this->matchNonStandardLineItem2($s);
        if ($parsed) {
            return $parsed;
        }
        Log::error("Can't parse Connatix line item: ".$s);
//        echo $s."\n";
        return $parsed;
    }

    private function matchNonStandardLineItem1($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe1, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 3 || $count == 5) {
                if ($count == 5) {
                    $retval["ad_size"] = $matches[4];
                }
                $retval["publisher_id"] = intval($matches[1]);
                $retval["slot"] = $matches[2];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem2($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe2, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 2) {
                $retval["publisher_name"] = trim($matches[1]);
            }
        }
        return $retval;
    }

    public function import($params = [], $writeToDb=TRUE) {

        ini_set('max_execution_time', 180);

        $source_id = $this->source_id();

        // Note that Connatix does not correctly handle the case where multiple dates are downloaded
        //  so we enforce downloading only a single date
        // We could add an artificial loop through multiple dates, but this runs this risk of
        //  timing out if too many dates are given.
        $params['start'] = $params['date'];
        $params['end'] = $params['date'];

        try {
            $res = $this->login();
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
        }

        try {
            $ads = $this->getDimensions([
                'start' => $params['start'],
                'end' => $params['end'],
                'name' => 'Ads'
            ]);

            $devices = $this->getDimensions([
                'start' => $params['start'],
                'end' => $params['end'],
                'name' => 'Devices'
            ]);

            $report = $this->download([
                'start' => $params['start'],
                'end' => $params['end']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }

        // @TODO - Is this the best place to clear out the table?
        $this->clearSourceMetrics($source_id, $params);

        $badPublisherIds = [];
        $consolidated = $this->processDownload($report['Table'], $ads, $devices, $badPublisherIds, $params);
        $this->logUnrecognizedImportedPublishers($badPublisherIds, $source_id);

        if ($consolidated && $writeToDb) {
            $this->writeRowsToMetricBySourceAndFullSplitDaily($consolidated);
        }

        $this->refreshMetrics($params);

        return true;
    }
}