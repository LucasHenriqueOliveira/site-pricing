<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Technorati extends \App\Data\Scrape {
    private $_token;
    private $_publisherId;
    private $_product_type_id = 5; // Display - @TODO - Don't hard code

    private $_importedKeys = ["Day", "Site_Name", "Placement_Name", "Country", "Ad_Size", "Sold_Impressions", "Earnings"];
    private $_siteNameRe = "/([^\s]*)\s+.*/i";
    private $_customLineItemRe = '/_(international|us)_(desktop|mobile)_([a-d]\b)/i';

    public function __construct($params = []) {
        $params['source_id'] = 4;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function login() {
        $res = $this->client()->request('POST', 'https://mycontango.technorati.com/authenticate', [
            'cookies' => $this->jar(),
            'json' => [
                'email' => $this->username(),
                'password' => $this->password()
            ]
        ]);
        $data = json_decode($res->getBody());

        if (!$data->token) {
            return false;
        }
        $this->_token = $data->token;
        $this->_publisherId = $data->user->publisher_id;
    }

    public function download($params) {

        $res = $this->client()->request('POST', 'https://mycontango.technorati.com/api/reporting/run', [
            'cookies' => $this->jar(),
            'headers' => [
                'token' => $this->token(),
                'Content-type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                "role_calculation" => "admin",
                "date_range_filter" => "Custom",
                "interval_type" => "Day",
                "delta_threshold" => "",
                "start_date_filter" => $params['start'],
                "end_date_filter" => $params['end'],
                "group_by_site" => true,
                "group_by_section" => true,
                "group_by_country" => true,
                "group_by_ad_size" => true,
                "publisher_filter" => $this->publisherId(),
                "site_filter" => "",
                "section_filter" => "",
                "country_filter" => "",
                "ad_size_filter" => "",
                "platform_filter" => "",
                "impression_type_filter" => "",
                "skip" => 0,
                "limit" => 100000,
                "sort" => ""
            ]
        ]);

        return json_decode($res->getBody());
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
            $report = $this->download([
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

        $report = json_decode(json_encode($report->data),true);

        foreach ($report as $row) {
            $result = $this->extractRow($row);

            if($result['error']){
                $this->setSourceStatus($source_id, 'Server Error', $result['error']['code'], $result['error']['info']);
            } else {
                try {
                    $arrData = $result['extracted'];
                    $arrData['source_id'] = $source_id;
                    $arrData["product_type_id"] = $product_type_id;                    
                    
                } catch (\Exception $e) {
                    $this->setSourceStatus($source_id, 'Server Error', 6, $e->getMessage());
                }

                try {
                    $this->setBySourceFullSplit($arrData);
                } catch (\Exception $e) {
                    $this->setSourceStatus($source_id, 'Server Error', 9, $e->getMessage());
                }
            }
        }

        $this->refreshMetrics($params);
        return true;
    }

    public function token() {
        return $this->_token;
    }

    public function publisherId() {
        return $this->_publisherId;
    }

    // $import is an associative array corresponding to a row of data
    public function extractRow($import) {
        // If the row is blank, we don't want to output an error
        if ($import) {
            // Check for missing fields
            $missing = $this->findMissingImportKeys($import);
            if ($missing) {
                $retval["error"] = ["code" => 7, "info" => $missing["values"]];
            } else {
                // See if we can get data out of the line item
                $line_item = $import["Placement_Name"];
                $parsed = $this->parseLineItem($line_item);
                if (!$parsed) {
                    // @TODO - Make the info more user friendly - maybe use the original line
                    $retval["error"] = ["code" => 7, "info" => $line_item];
                } else {
                    if (!$parsed["publisher_id"]) {
                        $publisher_id = $this->getPublisherIdFromSiteName($import["Site_Name"]);
                        if ($publisher_id) {
                            $parsed["publisher_id"] = $publisher_id;
                        } else {
                            $retval["error"] = ["code" => 6, "info" => $line_item];
                        }
                    }
                    $extracted = [];
                    $extracted["date"] = $this->formatDateTime($import["Day"]);
                    $extracted["publisher_id"] = $parsed["publisher_id"];
                    $extracted["gross_revenue"] = Source::extractNumber($import["Earnings"]);
                    $revenue_share = $this->getRevenueShare($parsed["publisher_id"]);
                    $extracted["net_revenue"] = $gross_revenue * (float)$revenue_share->client_fraction;
                    $extracted["impressions"] = Source::extractNumber($import["Sold_Impressions"]);

                    $country = Technorati::mapGeo($import["Country"]);
                    $extracted["geo"] = $this->chooseFromThree($country, $parsed["geo"], "n/a");
                    $extracted["ad_size"] = $this->chooseFromThree($import["Ad_Size"], $parsed["ad_size"], "n/a");
                    $extracted["slot"] = $this->chooseFromTwo($parsed["slot"], "n/a");
                    $extracted["device"] = $this->chooseFromTwo($parsed["device"], "n/a");

                    $retval["extracted"] = $extracted;
                }
            }
        }
        return $retval;
    }

    public function findMissingImportKeys($aa) {
        $missing = $this->array_keys_missing($this->_importedKeys, $aa);
        if ($missing) {
            $missingString = implode(",", $missing);
            if (in_array("Placement_Name", $missing)) {
                if (in_array("Site_Name", $missing)) {
                    $description = "N/A";
                } else {
                    $description = $aa["Site_Name"];
                }
            } else {
                $description = $aa["Placement_Name"];
            }
            return ["description" => $description, "values" => $missingString];
        }
        return null;
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
        $x = preg_match($this->_customLineItemRe, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 4) {
                $geo = $matches[1];
                $device = $matches[2];
                $slot_version = $matches[3];
                $retval["geo"] = Technorati::standardizeGeoFromNonStandard($geo);
                $retval["device"] = Technorati::standardizeDeviceFromNonStandard($device);
                $retval["slot"] = Technorati::getAdSlotFromDeviceAndVersion($retval["device"], $slot_version);
            }
        }
        return $retval;
    }

    public function getPublisherIdFromSiteName($n) {
        $siteNameLC = $this->extractSiteNameLC($n);
        return $this->getPublisherIdFromPublisherName($siteNameLC);
    }

    public function extractSiteNameLC($n) {
        $x = preg_match($this->_siteNameRe, strtolower($n), $matches);
        if ($x) {
            $retval = $matches[1];
        }
        return $retval;
    }

    public static function mapGeo($input) {
        $retval = NULL;
        $input_lc = strtolower($input);
        if ($input_lc === "us") {
            $retval = "us";
        } else if ($input_lc == "") {
            $retval = "n/a";
        } else {
            $retval = "in";
        }
        // @TODO - More validation?
        return $retval;
    }

    public static function standardizeGeoFromNonStandard($geo) {
        // $geo should already be lowercased
        if ($geo === "international") {
            $retval = "in";
        } else if ($geo === "us") {
            $retval = "us";
        }
        return $retval;
    }

    public static function standardizeDeviceFromNonStandard($device) {
        // $device should already be lowercased
        if ($device === "desktop") {
            $retval = "dsk";
        } else if ($device === "mobile") {
            $retval = "mob";
        }
        return $retval;
    }

    public static function getAdSlotFromDeviceAndVersion($device, $slot_version) {
        // $device and $slot_version should already be lowercased
        // @TODO - We assume banner, which is not necessarily the right thing to do
        return $device."-box-ad-".$slot_version;
    }

    private function formatDateTime($date) {
        return $date. ' 00:00:00';
    }


}