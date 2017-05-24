<?php

// Currently cannot get the full breakdown via the API

namespace App\Data\Source;

use Log;
use App\Data\Source;

class OpenX extends \App\Data\Source\OpenXBase {

     private $_customLineItemRe1 = '/^([0-9]{4,6})-.*-(mob|dsk|app)/i';
     private $_customLineItemRe2 = '/^([0-9]{4,6})-.*/i';
     private $_customLineItemRe3 = '/^206_(.*)/i';

    public function __construct($params = []) {
        $usEmailSubject = "OpenX206 - Agency Enterprise - US206";
        $allEmailSubject = "OpenX206 - Agency Enterprise - All206";
        $source_id = 17;
        $product_type_id = 5; // Display
        parent::__construct($params, $usEmailSubject, $allEmailSubject, $source_id, $product_type_id, 6);
    }

    public function extractRow($import, $geo, $source, $dateLookup) {
        // If the row is blank, we don't want to output an error
        $extracted = null;
        $line_item_field = $source->line_item_field;
        $date_field = $source->date_field;
        $gross_revenue_field = $source->gross_revenue_field;
        $impressions_field = $source->impressions_field;
        $ad_size_field = $source->ad_size_field;
        $device_field = $source->device_field;
        if ($import) {
            // See if we can get data out of the line item
            $line_item = $import[$line_item_field];
            if ($line_item  && $line_item !== "Total") {
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
                    $tempDate = $dateLookup[$import[$date_field]];
                    if ($tempDate) {
                        $device = OpenXBase::mapDevice($import[$device_field]);
                        $extracted["date"] = $this->_formatDateTime($tempDate);
                        $extracted["publisher_id"] = $parsed["publisher_id"];
                        $gross_revenue = Source::extractNumber($import[$gross_revenue_field]);
                        // search client_fraction in revenue_share table
                        $revenue_share = $this->getRevenueShare($extracted['publisher_id']);
                        $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;
                        $extracted['gross_revenue'] = $gross_revenue;

                        $extracted["impressions"] = Source::extractNumber($import[$impressions_field]);

                        $extracted["geo"] = $geo;
                        $extracted["ad_size"] = Source::cleanUpAdSize($this->chooseFromThree($import[$ad_size_field], $parsed["ad_size"], "n/a"));
                        $extracted["device"] = $this->chooseFromThree($device, $parsed["device"], "n/a");
                        // No slot right now
//                        $extracted["slot"] = $this->chooseFromTwo($parsed["slot"], "n/a");
                    } else {
                        $extracted["error"] = ["code" => 6, "info" => 'Date parsing error: '.$import[$date_field]];
                        return $extracted;
                    }
                }
            }
        }
        return $extracted;
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
        Log::error("Can't parse OpenX line item: ".$s);
//        echo $s."\n";
        return $parsed;
    }


    private function matchNonStandardLineItem1($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe1, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 3) {
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[2];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
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
                $retval["publisher_id"] = intval($matches[1]);
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem3($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe3, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 2) {
                $retval["publisher_name"] = $matches[1];
            }
        }
        return $retval;
    }

}