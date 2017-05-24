<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Sovrn extends \App\Data\Scrape
{
    private $_accessToken;
    private $_websites;
    private $_customLineItemRe1 = '/^([0-9]{4,6})-.*-(mob|dsk|app)-(ad)-([a-d])(-us|-in)?(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe2 = '/^([0-9]{4,6})-.*-(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_customLineItemRe3 = '/^([0-9]{4,6})-.*_(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])_([0-9]+x[0-9]+)/i';
    private $_customLineItemRe4 = '/^[A-Z]+_([0-9]{4,6})-.*-(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])/i';
    private $_customLineItemRe5 = '/^[A-Z]+_([0-9]{4,6})-.*(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])_([0-9]+x[0-9]+)/i';
    private $_customLineItemRe6 = '/^([0-9]{4,6})-.*-([0-9]+x[0-9]+)-(mob|dsk|app)-(us|in)-([a-d])/i';
    private $_customLineItemRe7 = '/^(.*[A-Z]+)([0-9]+x[0-9]+).*(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])/i';

    public function __construct($params = []) {
        $params['source_id'] = 2;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    // ================================================
    public function login()
    {
        try {
            $res = $this->client()->request('POST', 'https://api.sovrn.com/oauth/token', [
                'cookies' => $this->jar(),
                'form_params' => [
                    'grant_type' => 'password',
                    'username' => $this->username(),
                    'password' => $this->password(),
                    'client_id' => 'sovrn',
                    'client_secret' => 'sovrn'
                ],
                'headers' => [
                    'User-Agent' => $this->agent(),
                    'Referer' => 'https://meridian.sovrn.com/',
                    'Origin' => 'https://meridian.sovrn.com',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
                ]
            ]);
        } catch (\Exception $e) {
            // failed to log in
            return false;
        }
        $data = json_decode($res->getBody());
        if (!$data->access_token) {
            return false;
        }
        $this->_accessToken = $data->access_token;

        try {
            $res = $this->client()->request('GET', 'https://api.sovrn.com/account/user', [
                'cookies' => $this->jar(),
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken(),
                    'Referer' => 'https://meridian.sovrn.com/',
                    'Origin' => 'https://meridian.sovrn.com',
                    'User-Agent' => $this->agent()
                ]
            ]);
        } catch (\Exception $e) {
            // failed to GET necessary header fields
            return false;
        }
        $data = json_decode($res->getBody());
        if (!$data->websites) {
            return false;
        }
        $this->_websites = $data->websites;
        return true;
    }

    public static function mapGeo($input) {
        $retval = NULL;
        $input_lc = strtolower($input);
        if ($input_lc === "us") {
            $retval = "us";
        } else if ($input_lc === "other") {
            $retval = "in";
        }
        // @TODO - More validation?
        return $retval;
    }


    // ================================================
    public function download($params = [])
    {
        $result = $this->client()->request(
            'GET',
            'https://api.sovrn.com/download/adstats/csv'
            .'?site=all%20sites'
            .'&startDate='.$this->millisEpoch($params['start'])
            .'&endDate='.$this->millisEpoch($params['end'])
            .'&view=Yesterday'
            .'&breakout=true' // setting this true adds a date column
            .'&includeTagsWithNoRequests=false'
            .'&trafficType=DOMESTIC_AND_INTERNATIONAL'
            .'&country=US'
            .'&currency=USD'
            .'&iid=13068347',
            [
                'cookies' => $this->jar(),
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken(),
                    'Referer' => 'https://meridian.sovrn.com/',
                    'Origin' => 'https://meridian.sovrn.com'
                ]
            ]
        );

        $csvData = $result->getBody()->getContents();
        return($this->csvToAssoc($csvData, 6));
    }


    public function extractRow($import) {
        // If the row is blank, we don't want to output an error
        $extracted = null;
        if ($import) {
            // See if we can get data out of the line item
            $line_item = $import['Tag Name'];
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
                    $extracted["date"] = $this->formatDateTime($import["Date"]);
                    $extracted["publisher_id"] = $parsed["publisher_id"];
                    // @TODO - Get rid of hard coding
                    $gross_revenue = Source::extractNumber($import["Earnings"]);
                    // search client_fraction in revenue_share table
                    $revenue_share = $this->getRevenueShare($extracted['publisher_id']);
                    $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;
                    $extracted['gross_revenue'] = $gross_revenue;

                    // @TODO - Get rid of hard coding
                    $extracted["impressions"] = Source::extractNumber($import["Impressions"]);

                    $geoMapped = Sovrn::mapGeo($import['Traffic']);
                    $extracted['geo'] = $geoMapped;
                    // @TODO - Get rid of hard coding
                    $extracted["ad_size"] = $this->chooseFromThree($import["Tag Dimensions"], $parsed["ad_size"], "n/a");
                    $extracted["slot"] = $this->chooseFromTwo($parsed["slot"], "n/a");
                    $extracted["device"] = $this->chooseFromTwo($parsed["device"], "n/a");
                }
            }
        }
        return $extracted;
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
        $parsed = $this->matchNonStandardLineItem6($s);
        if ($parsed) {
            return $parsed;
        }
        $parsed = $this->matchNonStandardLineItem7($s);
        if ($parsed) {
            return $parsed;
        }
        Log::error("Can't parse Sovrn line item: ".$s);
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
            if ($count == 7) {
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[2];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["slot"] = implode("-", array_slice($matches, 2, 4));
                $retval["ad_size"] = $matches[6];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem4($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe4, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 6) {
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

    private function matchNonStandardLineItem5($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe5, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 7) {
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[2];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["slot"] = implode("-", array_slice($matches, 2, 4));
                $retval["ad_size"] = $matches[6];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem6($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe6, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 6) {
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[3];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["geo"] = $matches[4];
                $retval["slot"] = $matches[3]."-box-ad-".$matches[5];
                $retval["ad_size"] = $matches[2];
            }
        }
        return $retval;
    }

    private function matchNonStandardLineItem7($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe7, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 7) {
                $retval["publisher_name"] = $matches[1];
                $device = $matches[3];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["slot"] = implode("-", array_slice($matches, 3, 4));
                $retval["ad_size"] = $matches[2];
            }
        }
        return $retval;
    }


    public function processDownload($report, &$badPublisherIds) {
        $source_id = $this->source_id();
        $product_type_id = $this->product_type_id();
        $toDb = [];
        foreach ($report as $item) {
            $extracted = $this->extractRow($item);
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



    // ================================================
    public function import($params = [], $writeToDb=TRUE) {

        ini_set('max_execution_time', 180);

        $source_id = $this->source_id();
        try {
            $login = $this->login();
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

        // @TODO - Is this the best place to clear out the table?
        $this->clearSourceMetrics($source_id, $params);

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $reportCheck = $this->checkImportedDataLength($report);
        if(!$reportCheck) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $badPublisherIds = [];
        $consolidated = $this->processDownload($report, $badPublisherIds);
        $this->logUnrecognizedImportedPublishers($badPublisherIds, $source_id);

        if ($consolidated && $writeToDb) {
            $this->writeRowsToMetricBySourceAndFullSplitDaily($consolidated);
        }

        $this->refreshMetrics($params);

        return true;
    }

    // ================================================
    private function millisEpoch($date)
    {
        /* https://www.epochconverter.com/programming/php#date2epoch */
        $result = new \DateTime($date); /* format: MM/DD/YYYY */
        return(($result->format('U'))*1000);
    }

    // ================================================
    private function formatDateTime($monthLeadingSlashDelim) {
        $dt = new \DateTime($monthLeadingSlashDelim);
        return $dt->format("Y-m-d")." 00:00:00";
    }

    // ================================================
    public function earnings($params)
    {
        $res = $this->client()->request(
            'GET',
            'https://api.sovrn.com/earnings/breakout/all'
            .'?iid=13068347'
            .'&startDate='.$this->millisEpoch($params['start'])
            .'&endDate='.$this->millisEpoch($params['start'])
            .'&site='.$params['site']
            .'&country=US',
            [
                'cookies' => $this->jar(),
                'headers' =>
                [
                    'Authorization' => 'Bearer '.$this->accessToken(),
                    'Referer' => 'https://meridian.sovrn.com/',
                    'Origin' => 'https://meridian.sovrn.com/'
                ]
            ]
        );
        $data = json_decode($res->getBody());
        return $data;
    }

    // ================================================
    // I should probably fix the hardcoded IID?  Or is it fine?  It seems dangerous.  What if Publisher Desk
    // ever changes the credentials they're using with Sovrn?
    public function overview($params)
    {
        $res = $this->client()->request('GET', 'https://api.sovrn.com/overview/all?site='.$params['site'].'&startDate='.$this->millisEpoch($params['start']).'&endDate='.$this->millisEpoch($params['start']).'&iid=13068347', [
            'cookies' => $this->jar(),
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken(),
                'Referer' => 'https://meridian.sovrn.com/',
                'Origin' => 'https://meridian.sovrn.com/'
            ]
        ]);
        $data = json_decode($res->getBody());
        return $data;
    }

    // ================================================
    public function accessToken()
    {
        return $this->_accessToken;
    }

    // ================================================
    public function websites()
    {
        return $this->_websites;
    }
}

//{"access_token":"7ca0d376-8c44-44ba-8f1a-cf801373fe06","token_type":"bearer","expires_in":1295999,"scope":"read/write"}
//https://api.sovrn.com/account/user
//Authorization:Bearer 7ca0d376-8c44-44ba-8f1a-cf801373fe06
//https://api.sovrn.com/earnings/breakout/all?iid=13068347&startDate=1483228800000&endDate=1485907199999&site=wtf1.co.uk&country=US
//https://api.sovrn.com/overview/all?site=wtf1.co.uk&startDate=1483228800000&endDate=1485907199999&iid=13068347