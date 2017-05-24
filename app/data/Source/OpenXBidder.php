<?php

// Currently cannot get the full breakdown via the API

namespace App\Data\Source;

use Log;
use App\Data\Source;

class OpenXBidder extends \App\Data\Source\OpenXBase {

    public function __construct($params = []) {
        $usEmailSubject = "OpenX Bidder - Agency Enterprise - US";
        $allEmailSubject = "OpenX Bidder - Agency Enterprise - All";
        $source_id = 18;
        $product_type_id = 5; // Display
        parent::__construct($params, $usEmailSubject, $allEmailSubject, $source_id, $product_type_id, 8);
    }


    public function extractRow($import, $geo, $source, $dateLookup) {
        // If the row is blank, we don't want to output an error
        $extracted = null;
            // @TODO - Get rid of hard coding
        $line_item_field = $source->line_item_field;
        $date_field = $source->date_field;
        $gross_revenue_field = $source->gross_revenue_field;
        $impressions_field = $source->impressions_field;
        $ad_size_field = $source->ad_size_field;
        $device_field = $source->device_field;
        $publisher_name_field = $source->publisher_name_field;
        if ($import) {
            // See if we can get data out of the line item
            $line_item = $import[$line_item_field];
            if ($line_item) {
                $parsed = $this->parseLineItem($line_item);
                if (!$parsed || !$parsed["publisher_id"]) {
                    $publisher_name = $import[$publisher_name_field];
                    $publisher_id = $this->getPublisherIdFromPublisherName($publisher_name);
                    if ($publisher_id) {
                        $parsed["publisher_id"] = $publisher_id;
                    } else {
                        Log::error("Don't recognize OpenXBidder publisher: ".$publisher_name);
                        $extracted["error"] = ["code" => 6, "info" => $publisher_name." ".$line_item];
                        return $extracted;
                    }
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
        return $extracted;
    }


    public function matchNonStandardLineItem($s) {
        Log::error("Can't parse OpenXBidder line item: ".$s);
//        echo $s."\n";
        return null;
    }

}