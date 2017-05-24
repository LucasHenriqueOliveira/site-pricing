<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Rubicon extends \App\Data\Scrape {

    // @TODO - Note that this is currently hard-coded.
    private $_token;
    private $_customLineItemRe1 = '/^([0-9]{4,6})-.*-(mob|dsk|app)-(ad)-([a-d])(-us|-in)?(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe2 = '/^([0-9]{4,6})-.*-(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe3 = '/^([0-9]{4,6})-.*-(us|in)-(mob|dsk|app)-([a-d])(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe4 = '/^(.*)-(us|in)-(mob|dsk|app)-(ad)-([a-d])(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe5 = '/^([0-9]{4,6})-.*-(ad)-([a-d])(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';

    public function __construct($params = []) {
        $params['source_id'] = 1;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function login() {

        try {
            $res = $this->client()->request('POST', 'https://login.rubiconproject.com//form/login/', [
                'cookies' => $this->jar(),
                'headers' => [
                    'User-Agent' => $this->agent()
                ],
                'form_params' => [
                    'username' => $this->username(),
                    'password' => $this->password(),
                    'redirect_uri' => 'https://revv.rubiconproject.com'
                ],
            ]);
        } catch (\Exception $e) {
            // failed to log in
            return false;
        }

        try {
            $res = $this->client()->request('GET', 'https://login.rubiconproject.com/oauth/authorize?client_id=9&response_type=token&redirect_uri=https://platform.rubiconproject.com/&url_fragment=', [
                'cookies' => $this->jar(),
                'headers' => [
                    'User-Agent' => $this->agent(),
                    'Referer' => 'https://platform.rubiconproject.com/'
                ],
                'allow_redirects' => [
                    'referer'         => true,
                    'track_redirects' => true
                ]
            ]);
        } catch (\Exception $e) {
            // failed to GET token
            return false;
        }
        // failed to get token
        if (!$res->getHeaders()['X-Guzzle-Redirect-History']) {
            return false;
        }

        parse_str(parse_url($res->getHeaders()['X-Guzzle-Redirect-History'][0])['fragment'], $token);
        $this->_token = $token['access_token'];
        return true;
    }

    private function _formatDate($date) {
        return (new \DateTime($date))->format('Y-m-d');
    }

    private function _formatDateTime($date) {
        return $date. ' 00:00:00';
    }

    public function downloadAllCountries($params = []) {
        $timestamp = intval(microtime(TRUE)*1000);
        $res = $this->client()->request('POST',
            'https://platform.rubiconproject.com/services/reporting/actions/export/form/?access_token='.$this->token(), [
            'form_params' => [
                // @todo: convert to a php array to its easier to read
                'exportReport' => '{
                    "report":{
                        "label":"Agency Enterprise",
                        "currency":"USD",
                        "dateRange":{
                            "dateRangeString":"custom",
                            "start":"'.$this->_formatDate($params['start']).'",
                            "end":"'.$this->_formatDate($params['end']).'",
                            "reportDate":{
                                "start":"'.$this->_formatDate($params['start']).'",
                                "end":"'.$this->_formatDate($params['end']).'"
                            }
                        },
                        "columns":[
                            {"id":"Time_Date",
                            "label":"Date",
                            "sortDirection":null,
                            "displayType":"datetime",
                            "filterType":"none",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["blr","standard"],
                            "deprecated":false
                            },
                            {"id":"Zone_Name",
                            "label":"Zone",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"search",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["blr","standard"],
                            "deprecated":false
                            },
                            {"id":"Performance_NetworkImps",
                            "label":"Paid Impressions",
                            "sortDirection":null,
                            "displayType":"integer",
                            "filterType":"num",
                            "isFeature":false,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false
                            },
                            {"id":"Performance_NetworkRevenue",
                            "label":"Publisher Gross Revenue",
                            "sortDirection":null,
                            "displayType":"money",
                            "filterType":"none","isFeature":false,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false
                            },
                            {"id":"Site_Name",
                            "label":"Site",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"search",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["blr","standard"],
                            "deprecated":false
                            },
                            {"id":"Size_Dimensions",
                            "label":"Size Dimensions",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"enum",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false
                            }],
                        "filters":[],
                        "excludes":[],
                        "limit":0,
                        "graph":{
                            "id":26796860,
                            "type":"line",
                            "axes":{
                                "x":null,
                                "y":[]
                            }
                        },
                        "dataSource":"standard",
                        "dataLastUpdated":null,
                        "groupBy":[],
                        "schedule":{
                            "frequency":"none",
                            "hour":"0",
                            "dayOfTheWeek":null,
                            "dayOfTheMonth":null,
                            "emails":[],
                            "format":"csv"
                        },
                        "status":"active",
                        "noRevenueStatus":true,
                        "hasEstimatedData":"undefined",
                        "objectMetaData":{
                            "sparse":false
                        }
                    },
                    "format":"csv",
                    "timestamp":'.$timestamp.'
                }'
            ]
        ]);

        $data = $this->csvToAssoc($res->getBody()->getContents());
        return $data;
    }

    public function downloadUS($params = []) {
        $timestamp = intval(microtime(TRUE)*1000);
        $res = $this->client()->request('POST',
            'https://platform.rubiconproject.com/services/reporting/actions/export/form/?access_token='.$this->token(), [
            'form_params' => [
                // @todo: convert to a php array to its easier to read
                'exportReport' => '{
                    "report":{
                        "label":"Agency Enterprise US",
                        "currency":"USD",
                        "dateRange":{
                            "dateRangeString":"custom",
                            "start":"'.$this->_formatDate($params['start']).'",
                            "end":"'.$this->_formatDate($params['end']).'",
                            "reportDate":{
                                "start":"'.$this->_formatDate($params['start']).'",
                                "end":"'.$this->_formatDate($params['end']).'"
                            }
                        },
                        "columns":[
                            {"id":"Time_Date",
                            "label":"Date",
                            "sortDirection":null,
                            "displayType":"datetime",
                            "filterType":"none",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["blr","standard"],
                            "deprecated":false
                            },
                            {"id":"Zone_Name",
                            "label":"Zone",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"search",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["blr","standard"],
                            "deprecated":false
                            },
                            {"id":"Performance_NetworkImps",
                            "label":"Paid Impressions",
                            "sortDirection":null,
                            "displayType":"integer",
                            "filterType":"num",
                            "isFeature":false,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false
                            },
                            {"id":"Performance_NetworkRevenue",
                            "label":"Publisher Gross Revenue",
                            "sortDirection":null,
                            "displayType":"money",
                            "filterType":"none","isFeature":false,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false
                            },
                            {"id":"Site_Name",
                            "label":"Site",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"search",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["blr","standard"],
                            "deprecated":false
                            },
                            {"id":"Size_Dimensions",
                            "label":"Size Dimensions",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"enum",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false
                            },
                            {"id":"Country_Name",
                            "label":"Country",
                            "sortDirection":null,
                            "displayType":"string",
                            "filterType":"search",
                            "isFeature":true,
                            "isWeighted":false,
                            "isHistogram":false,
                            "dataSources":["standard"],
                            "deprecated":false}],
                        "filters":[
                            {"column":
                            {"id":"Country_Name",
                            "label":"Country"},
                            "values":[
                                {"label":"United States",
                                "value":"3229",
                                "action":null}],
                            "id":"generic_id_95946"}],
                        "excludes":[],
                        "limit":0,
                        "graph":{
                            "id":27716838,
                            "type":"line",
                            "axes":{
                                "x":null,
                                "y":[]
                            }
                        },
                        "dataSource":"standard",
                        "dataLastUpdated":null,
                        "groupBy":[],
                        "schedule":{
                            "frequency":"none",
                            "hour":"0",
                            "dayOfTheWeek":null,
                            "dayOfTheMonth":null,
                            "emails":[],
                            "format":"csv"
                        },
                        "status":"active",
                        "noRevenueStatus":true,
                        "hasEstimatedData":"undefined",
                        "objectMetaData":{
                            "sparse":false
                        }
                    },
                    "format":"csv",
                    "timestamp":'.$timestamp.'
                }'
            ]
        ]);

        $data = $this->csvToAssoc($res->getBody()->getContents());
        return $data;
    }


    public function download($params = []) {
        $allData = $this->downloadAllCountries($params);
        $usData = $this->downloadUS($params);
        $data = ['all' => &$allData, 'us' => &$usData];
        return $data;
    }


    public function token() {
        return $this->_token;
    }

    public function processDownload($geo, $report, &$badPublisherIds) {
        $source_id = $this->source_id();
        $product_type_id = $this->product_type_id();
        $toDb = [];
        foreach ($report as $item) {
            $extracted = $this->extractRow($item, $geo);
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

    public function extractRow($import, $geo) {
        // If the row is blank, we don't want to output an error
        $extracted = null;
        if ($import) {
            // See if we can get data out of the line item
            $line_item = $import['Zone'];
            if ($line_item) {
                $parsed = $this->parseLineItem($line_item);
                if (!$parsed) {
                    // @TODO - Make the info more user friendly - maybe use the original line
                    $extracted["error"] = ["code" => 7, "info" => $line_item];
                    return $extracted;
                } else {
                    if (!$parsed["publisher_id"] && $parsed["publisher_name"]) {
                        $publisher_id = $this->getPublisherIdFromPublisherName($parsed["publisher_name"]);
                        if ($publisher_id) {
                            $parsed["publisher_id"] = $publisher_id;
                        } else {
                            $extracted["error"] = ["code" => 6, "info" => $line_item];
                            return $extracted;
                        }
                    } else if (!$parsed["publisher_id"]){
                        $extracted["error"] = ["code" => 7, "info" => $line_item];
                        return $extracted;
                    }
                    $extracted = [];
                    $extracted["date"] = $this->_formatDateTime($import["Date"]);
                    $extracted["publisher_id"] = $parsed["publisher_id"];
                    // @TODO - Get rid of hard coding
                    $gross_revenue = Source::extractNumber($import["Publisher Gross Revenue"]);
                    // search client_fraction in revenue_share table
                    $revenue_share = $this->getRevenueShare($extracted['publisher_id']);
                    $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;
                    $extracted['gross_revenue'] = $gross_revenue;

                    // @TODO - Get rid of hard coding
                    $extracted["impressions"] = Source::extractNumber($import["Paid Impressions"]);

                    $extracted["geo"] = $geo;
                    // @TODO - Get rid of hard coding
                    $extracted["ad_size"] = $this->chooseFromThree($import["Size Dimensions"], $parsed["ad_size"], "n/a");
                    $extracted["slot"] = $this->chooseFromTwo($parsed["slot"], "n/a");
                    $extracted["device"] = $this->chooseFromTwo($parsed["device"], "n/a");
                }
            }
        }
        return $extracted;
    }

    public function import($params = [], $writeToDb=TRUE) {

        ini_set('max_execution_time', 300);
        $source_id = $this->source_id();
        try {
            $res = $this->login();
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
        }

        try {
            $reports = $this->download([
                'start' => $params['start'],
                'end' => $params['end']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }

        // @TODO - Is this the best place to clear out the table?
        $this->clearSourceMetrics($source_id, $params);

        $usReport = $reports['us'];
        $allReport = $reports['all'];

        if(!$usReport || !$allReport) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $usReportCheck = $this->checkImportedDataLength($usReport);
        $allReportCheck = $this->checkImportedDataLength($allReport);

        if(!$usReportCheck || !$allReportCheck) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $badPublisherIds = [];
        $usConsolidated = $this->processDownload('us', $usReport, $badPublisherIds);
        $allConsolidated = $this->processDownload('in', $allReport, $badPublisherIds);
        $intlConsolidated = $this->extractInternationalFromAllAndUSArrays($allConsolidated, $usConsolidated);

        $this->logUnrecognizedImportedPublishers($badPublisherIds, $source_id);

        if ($usConsolidated && $writeToDb) {
            $this->writeRowsToMetricBySourceAndFullSplitDaily($usConsolidated);
        }
        if ($intlConsolidated && $writeToDb) {
            $this->writeRowsToMetricBySourceAndFullSplitDaily($intlConsolidated);
        }


        $this->refreshMetrics($params);
        return true;
    }

    public function parseLineItem($s) {
        $standardParse = $this->matchStandardDisplayLineItem($s);
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
        $parsed = $this->matchNonStandardLineItem3($s);
        if ($parsed) {
            return $parsed;
        }
        $parsed = $this->matchNonStandardLineItem4($s);
        if ($parsed) {
            return $parsed;
        }
        $parsed = $this->matchNonStandardLineItem5($s);
        if ($parsed) {
            return $parsed;
        }
        Log::error("Can't parse Rubicon line item: ".$s);
//        echo $s."\n";
        return $parsed;
    }

    private function matchNonStandardLineItem1($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe1, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 5 || $count == 6 || $count == 8) {
                if ($count == 8) {
                    $retval["ad_size"] = $matches[7];
                }
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[2];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                if ($count == 6) {
                    // Note that we are getting geo from the report, so we really don't need geo here
                    $retval["geo"] = substr($matches[5], 1);
                } else if (($count == 8) && ($matches[5] !== "")) {
                    $retval["geo"] = substr($matches[5], 1);
                }
                $retval["slot"] = $matches[2]."-box-".$matches[3]."-".$matches[4];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem2($s) {
        $x = preg_match($this->_customLineItemRe2, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 8 || $count == 6) {
                if ($count == 8) {
                    $retval["ad_size"] = $matches[7];
                }
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[2];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["slot"] = implode("-", array_slice($matches, 2, 4));
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem3($s) {
        $x = preg_match($this->_customLineItemRe3, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 5 || $count == 7) {
                if ($count == 7) {
                    $retval["ad_size"] = $matches[6];
                }
                $retval["publisher_id"] = intval($matches[1]);
                $retval["geo"] = $matches[2];
                $device = $matches[3];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["slot"] = $matches[3]."-box-ad-".$matches[4];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem4($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe4, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 8 || $count == 6) {
                if ($count == 8) {
                    $retval["ad_size"] = $matches[7];
                }
                $retval["publisher_name"] = $matches[1];
                $retval["geo"] = $matches[2];
                $device = $matches[3];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["slot"] = $matches[3]."-box-".$matches[4]."-".$matches[5];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem5($s) {
        $x = preg_match($this->_customLineItemRe5, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 4 || $count == 6) {
                if ($count == 6) {
                    $retval["ad_size"] = $matches[5];
                }
                $retval["publisher_id"] = intval($matches[1]);
            }
        }
        return $retval;
    }




}