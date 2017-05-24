<?php

namespace App\Data\Source;

use Log;
use App\Data\Source;

class Aol extends \App\Data\Email {

    private $isLoggedIn;
    private $_subject;

    // We are doing this here instead of in the database because it is easier to change with the column validators above
    private $_date_field = "Key(Event Date)";
    private $_publisher_name_field = "Key(Site Name)";
    private $_line_item_field = "Key(Placement Name)";
    private $_ad_size_field = "Key(Placement Size)";
    private $_geo_field = "Key(User Country)";
    private $_impressions_field = "Sum(API Won Impressions)";
    private $_gross_revenue_field = "Sum(API Won Revenue)";
    // This seems to be 0 in the reports right now, so use eCPM to back out the revenue value
    private $_ecpm_field = "Cal(API eCPM)";

    private $_customLineItemRe1 = '/^([0-9]{4,6})-.*-(us|in)-([0-9]+x[0-9]+)-(mob|dsk|app)-(ad)-([a-d])/i';
    private $_customLineItemRe2 = '/^.* Mobile API ([0-9]+x[0-9]+)/i';
    private $_customLineItemRe3 = '/^.* API ([0-9]+x[0-9]+)/i';
    private $_publisherNameRe = '/^([^\s]*).*/i';

    protected $_spreadsheetCols;

    public function __construct($params = []) {
        $this->isLoggedIn = false;

        $this->_subject = "The Publisher Desk Daily API Report";

        $params['source_id'] = 14;
        $params['product_type_id'] = 5; // Display

        $this->_spreadsheetCols = [
            $this->_date_field => 0,
            "Key(Pub Name)" => 1,
            $this->_publisher_name_field => 2,
            $this->_line_item_field => 3,
            $this->_ad_size_field => 4,
            $this->_geo_field => 5,
            "Sum(API Total Responses)" => 6,
            $this->_impressions_field => 7,
            $this->_gross_revenue_field => 8,
            $this->_ecpm_field => 9,
            "Cal(API Win Rate)" => 10
        ];

        parent::__construct($params);
    }

    public function login(){
        parent::login();
        $this->isLoggedIn = true;
    }

    public function import($params=[], $writeToDb=TRUE) {

        ini_set('max_execution_time', 600);

        $source_id = $this->source_id();
        if (!$this->isLoggedIn) {
            try {
                $res = $this->login();
            } catch (\Exception $e) {
                return $this->setSourceStatus($source_id, 'Email login error', 2, $e->getMessage());
            }
        }

        $report = $this->importFromMailbox();

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $reportCheck = $this->checkImportedDataLength($report);
        if(!$reportCheck) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

//        $source = $this->source();

        $date_field = $this->_date_field;
        $dateSet = $this->getMinAndMaxDates($date_field, "m/d/Y", $report);
        $minDate = $dateSet['min'];
        $maxDate = $dateSet['max'];
        if (!minDate || !maxDate) {
            return $this->setSourceStatus($source_id, 'Server Error', 8, 'AOL: cannot parse dates in xlsx');

        }
        $this->clearSourceMetrics($source_id, ['start' => $minDate, 'end' => $maxDate]);

        $badPublisherIds = [];
        $consolidated = $this->processDownload($report, $badPublisherIds);

        $this->logUnrecognizedImportedPublishers($badPublisherIds, $source_id);

        if ($consolidated && $writeToDb) {
            $this->writeRowsToMetricBySourceAndFullSplitDaily($consolidated);
        }

        if ($this->doClearInbox()) {
            $this->moveEmailsOutOfInbox();
        }
        $this->refreshMetrics(['start' => $minDate, 'end' => $maxDate]);
    }

    public function processDownload($report, &$badPublisherIds) {
        $source = $this->source();
        $source_id = $this->source_id();
        $product_type_id = $this->product_type_id();
        $dateLookup = $this->dateLookup();
        $toDb = [];
        foreach ($report as $item) {
            $extracted = $this->extractRow($item, $dateLookup);
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

    private function importFromMailbox() {

        $subject = $this->_subject;
        $source_id = $this->source_id();
        $searchResult = $this->getMailBySubject($subject);
        if (!searchResult) {
            $this->setSourceStatus($source_id, 'Server Error', 10, $subject);
            return null;
        }

        $mail = $searchResult['mail'];
        $mailNumber = $searchResult['mailNumber'];
        // The mailbox date is UTC in gmail
        if (!$mail) {
            $this->setSourceStatus($source_id, 'Server Error', 10, $subject);
            return null;
        }

        $files = $mail->getAttachments();
        if (!$files || count($files) == 0) {
            $this->setSourceStatus($source_id, 'Server Error', 11, $subject);
            return null;
        }
        foreach ($files as $file) {
            $filePath = $file->filePath;
            break;
        }
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        } catch (\Exception $e) {
            unlink($filePath);
            $this->setSourceStatus($source_id, 'Server Error', 13, $subject);
            return null;
        }
        unlink($filePath);
        if (!$spreadsheet) {
            $this->setSourceStatus($source_id, 'Server Error', 13, $subject);
            return null;
        }
        $ws = $spreadsheet->getSheet(0);

        $import = $this->worksheetToArray($ws);
        $this->addToImported($mailNumber);
        return $import;
    }

    public static function mapGeo($input) {
        $retval = NULL;
        $input_lc = strtolower($input);
        if ($input_lc === "usa") {
            $retval = "us";
        } else {
            $retval = "in";
        }
        return $retval;
    }

    private function _formatDateTime($date){
        $date = date("Y-m-d", strtotime($date));
        return $date . ' 00:00:00';
    }

    private function worksheetToArray($ws) {
        $source_id = $this->source_id();
        if ($ws) {
            $headerRow = 1;
            $badHeaders = $this->checkSpreadsheetBadHeaders($ws, $headerRow);
            if ($badHeaders) {
                $badHeadersString = implode($badHeaders, ", ");
                $this->setSourceStatus($source_id, 'Server Error', 14, "Bad headers: ".$badHeadersString);
                return null;
            }

            $date_field = $this->_date_field;
            $dateColNum = $this->_spreadsheetCols[$date_field];
            $publisher_name_field = $this->_publisher_name_field;
            $publisherNameColNum = $this->_spreadsheetCols[$publisher_name_field];
            $line_item_field = $this->_line_item_field;
            $lineItemColNum = $this->_spreadsheetCols[$line_item_field];
            $ad_size_field = $this->_ad_size_field;
            $adSizeColNum = $this->_spreadsheetCols[$ad_size_field];
            $geo_field = $this->_geo_field;
            $geoColNum = $this->_spreadsheetCols[$geo_field];
            $impressions_field = $this->_impressions_field;
            $impressionsColNum = $this->_spreadsheetCols[$impressions_field];
            $gross_revenue_field = $this->_gross_revenue_field;
            $grossRevenueColNum = $this->_spreadsheetCols[$gross_revenue_field];
            $ecpm_field = $this->_ecpm_field;
            $ecpmColNum = $this->_spreadsheetCols[$ecpm_field];

            $status = TRUE;

            $curRow = $headerRow + 1;
            while ($status) {
                $date = $ws->getCellByColumnAndRow($dateColNum, $curRow)->getFormattedValue();
                if (!$date) {
                    $status = FALSE;
                } else {
                    $obj = [];
                    $obj[$date_field] = $date;
                    // For some reason, the xlsx from AOL need to use formatted values
                    $obj[$publisher_name_field] = $ws->getCellByColumnAndRow($publisherNameColNum, $curRow)->getFormattedValue();
                    $obj[$line_item_field] = $ws->getCellByColumnAndRow($lineItemColNum, $curRow)->getFormattedValue();
                    $obj[$ad_size_field] = $ws->getCellByColumnAndRow($adSizeColNum, $curRow)->getFormattedValue();
                    $obj[$geo_field] = $ws->getCellByColumnAndRow($geoColNum, $curRow)->getFormattedValue();

                    $obj[$impressions_field] = $ws->getCellByColumnAndRow($impressionsColNum, $curRow)->getValue();
                    $obj[$gross_revenue_field] = $ws->getCellByColumnAndRow($grossRevenueColNum, $curRow)->getValue();
                    $obj[$ecpm_field] = $ws->getCellByColumnAndRow($ecpmColNum, $curRow)->getValue();
                    $arr[] = $obj;
                }
                $curRow++;
            }
        }

        return $arr;
    }

    public function extractRow($import, $dateLookup) {
        // If the row is blank, we don't want to output an error
        $extracted = null;
        $date_field = $this->_date_field;
        $publisher_name_field = $this->_publisher_name_field;
        $line_item_field = $this->_line_item_field;
        $ad_size_field = $this->_ad_size_field;
        $geo_field = $this->_geo_field;
        $impressions_field = $this->_impressions_field;
        $ecpm_field = $this->_ecpm_field;
        if ($import) {
            // See if we can get data out of the line item
            $line_item = $import[$line_item_field];
            if ($line_item) {
                $parsed = $this->parseLineItem($line_item);
                if (!$parsed) {
                    // @TODO - Make the info more user friendly - maybe use the original line
                    $extracted["error"] = ["code" => 7, "info" => $line_item];
                    return $extracted;
                } else {
                    if (!$parsed["publisher_id"]) {
                        $publisher_name = $import[$publisher_name_field];
                        $parsedPN = $this->parseForPublisherName($publisher_name);
                        if ($parsedPN) {
                            $publisher_id = $this->getPublisherIdFromPublisherName($parsedPN);
                            if ($publisher_id) {
                                $parsed["publisher_id"] = $publisher_id;
                            } else {
                                $extracted["error"] = ["code" => 6, "info" => $line_item];
                                return $extracted;
                            }
                        } else {
                            $extracted["error"] = ["code" => 7, "info" => $line_item.", ".$publisher_name];
                            return $extracted;                            
                        }
                    }
                    $extracted = [];
                    $tempDate = $dateLookup[$import[$date_field]];
                    if ($tempDate) {
                        $extracted["date"] = $this->_formatDateTime($tempDate);
                        $extracted["publisher_id"] = $parsed["publisher_id"];

                        $ecpm = Source::extractNumber($import[$ecpm_field]);
                        $extracted["impressions"] = Source::extractNumber($import[$impressions_field]);
                        // search client_fraction in revenue_share table
                        $revenue_share = $this->getRevenueShare($extracted['publisher_id']);

                        // This needs to be done this way because gross revenue is currently coming in as 0
                        $gross_revenue = $ecpm * $extracted["impressions"] / 1000.0;
                        $extracted['gross_revenue'] = $gross_revenue;
                        $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;

                        $geo = Aol::mapGeo($import[$geo_field]);
                        $extracted["geo"] = $this->chooseFromThree($geo, $parsed["geo"], "n/a");
                        $extracted["ad_size"] = Source::cleanUpAdSize($this->chooseFromThree($import[$ad_size_field], $parsed["ad_size"], "n/a"));
                        $extracted["device"] = $this->chooseFromTwo($parsed["device"], "n/a");
                        $extracted["slot"] = $this->chooseFromTwo($parsed["slot"], "n/a");
                    } else {
                        $extracted["error"] = ["code" => 6, "info" => 'Date parsing error: '.$import[$date_field]];
                        return $extracted;
                    }
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
        Log::error("Can't parse AOL line item: ".$s);
//        echo $s."\n";
        return $parsed;
    }

    private function matchNonStandardLineItem1($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe1, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 7) {
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[4];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["geo"] = $matches[2];
                $retval["slot"] = $matches[4]."-box-".$matches[5]."-".$matches[6];
                $retval["ad_size"] = $matches[3];
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
                $device = "mob";
                $retval["device"] = $device;
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
                $device = "dsk";
                $retval["device"] = $device;
            }
        }
        return $retval;
    }

    public function parseForPublisherName($s) {
        $retval = null;
        $x = preg_match($this->_publisherNameRe, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 2) {
                $retval = $matches[1];
                if ($retval === '') {
                    $retval = null;
                }
            }
        }
        return $retval;
    }

}