<?php

namespace App\Data\Source;

use PHPHtmlParser\Dom;
use Log;
use App\Data\Source;

class Teads extends \App\Data\Email {

    private $isLoggedIn;
    private $_subject;

   // We are doing this here instead of in the database because it is easier to change with the column validators above
    private $_date_field = "day";
    private $_line_item_field = "placement";
    private $_device_field = "device";
    private $_impressions_field = "start";
    private $_gross_revenue_field = "income_converted_usd";

    private $_customLineItemRe1 = '/^.* - (us|in)-inread-cp-(.*)/i';
    private $_customLineItemRe2 = '/^.* - inread-ros-(.*)/i';
    private $_linkRe = '/href=\"(https:[^\"]*)\"/i';
    private $_badLinkRe = '/unsubscribe/i';

    private $_remainingHeaderValues;
    protected $_spreadsheetCols;

    public function __construct($params = []) {
        $this->isLoggedIn = false;

        $this->_subject = "Your scheduled reporting - Publisher Desk Daily Report";

        $params['source_id'] = 23;
        $params['product_type_id'] = 3; // Native

        // Note that these are not all the columns, but the column order switches around, unfortunately
        $this->_spreadsheetCols = [
            $this->_date_field => 0,
            $this->_line_item_field => 1,
            $this->_device_field => 2,
        ];

        $this->_remainingHeaderValues = [$this->_impressions_field => 1, $this->_gross_revenue_field =>1];

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


        $date_field = $this->_date_field;
        $dateSet = $this->getMinAndMaxDates($date_field, "Y/m/d", $report);
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

    public function extractRow($import, $dateLookup) {
        // If the row is blank, we don't want to output an error
        $extracted = null;

        $date_field = $this->_date_field;
        $line_item_field = $this->_line_item_field;
        $device_field = $this->_device_field;
        $impressions_field = $this->_impressions_field;
        $gross_revenue_field = $this->_gross_revenue_field;

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
                    if (!$parsed["publisher_id"] && $parsed["publisher_name"]) {
                        $publisher_name = $parsed["publisher_name"];
                        $publisher_id = $this->getPublisherIdFromPublisherName($publisher_name);
                        if ($publisher_id) {
                            $parsed["publisher_id"] = $publisher_id;
                        } else {
                            $extracted["error"] = ["code" => 6, "info" => $line_item];
                            return $extracted;
                        }
                    } else {
                        $extracted["error"] = ["code" => 7, "info" => $line_item];
                        return $extracted;
                    }
                    $extracted = [];
                    $tempDate = $dateLookup[$import[$date_field]];
                    if ($tempDate) {
                        $extracted["date"] = $this->_formatDateTime($tempDate);
                        $extracted["publisher_id"] = $parsed["publisher_id"];

                        $extracted["impressions"] = Source::extractNumber($import[$impressions_field]);
                        // search client_fraction in revenue_share table
                        $revenue_share = $this->getRevenueShare($extracted['publisher_id']);

                        $gross_revenue = Source::extractNumber($import[$gross_revenue_field]);
                        $extracted['gross_revenue'] = $gross_revenue;
                        $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;

                        $geo = Teads::mapGeo($parsed["geo"]);
                        $device = Teads::mapDevice($import[$device_field]);
                        $extracted["geo"] = $this->chooseFromTwo($geo, "n/a");
                        $extracted["device"] = $this->chooseFromThree($device, $parsed["device"], "n/a");

                         // Note that we are not extracting slot or ad size from the line item for now, though this could be added later
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
        Log::error("Can't parse Teads line item: ".$s);
//        echo $s."\n";
        return $parsed;
    }

    private function matchNonStandardLineItem1($s) {
        $retval = null;
        $x = preg_match($this->_customLineItemRe1, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 3) {
                $retval["publisher_name"] = $matches[2];
                $retval["geo"] = $matches[1];
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
                $retval["publisher_name"] = $matches[1];
                // @TODO - Note that we assume US here, because it seems like all of teads is US?
                $retval["geo"] = "us";
           }
        }
        return $retval;
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


        $mailBody = $mail->textHtml;
//        $mailBody = $mail->textPlain;
        if (!$mailBody) {
            $this->setSourceStatus($source_id, 'Server Error', 15, $subject);
            return null;
        }

//        $link = $this->getReportLinkFromHtml($mailBody);
       // Note that there is an html attachment, but PHPHtmlParser\Dom has issues parsing it,
        //  so we will parse the plain text for the link
        $link = $this->getReportLinkFromPlainText($mailBody);
        if (!$link) {
            $this->setSourceStatus($source_id, 'Server Error', 12, $subject);
            return null;
        }

        $contents = file_get_contents($link);

        $filePath = tempnam('/tmp','TEADS');
        file_put_contents($filePath, $contents);
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

    public static function mapGeo($s) {
        return $s;
    }

    public static function mapDevice($s) {
        $sLC = strtolower($s);
        if ($sLC === 'desktop') {
            return 'dsk';
        }
        return 'mob';
    }

    private function _formatDateTime($date){
        return $date. ' 00:00:00';
    }


    private function getReportLinkFromPlainText($body) {
        $link = $this->parseForLink($body);
        if ($link) {
            $link = preg_replace('/\s/', '', $link);
        }
        return $link;
    }

    private function getReportLinkFromHtml($body) {
        $dom = new Dom;
        $dom->loadStr($body, ['strict' => false, 'whitespaceTextNode' => false]);
        $links = $dom->find('a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (strpos($href, 'encodedreport')) {
                return str_replace(' ', '', $href);
            }
        }
        return null;
    }

    public function parseForLink($s) {
        $retval = null;
        $x = preg_match_all($this->_linkRe, $s, $matches, PREG_SET_ORDER);
        if ($x) {
            for ($i = 0; $i < $x; $i++) {
                $match = $matches[$i];
                $count = count($match);
                if ($count == 2) {
                    $retval = $match[1];
                    if ($retval === '') {
                        $retval = null;
                    } else {
                        $bad = preg_match($this->_badLinkRe, $retval, $ms);
                        if ($bad) {
                            $retval = null;
                        } else {
                            break;
                        }
                    }
                }
            }
        }
        return $retval;
    }

    private function worksheetToArray($ws) {
        $source_id = $this->source_id();
        if ($ws) {
            $headerRow = 16;
            $badHeaders = $this->checkSpreadsheetBadHeaders($ws, $headerRow);
            if ($badHeaders) {
                $badHeadersString = implode($badHeaders, ", ");
                $this->setSourceStatus($source_id, 'Server Error', 14, "Bad headers: ".$badHeadersString);
                return null;
            }

            // We need to do this because of changing header order
            $this->fillRemainingHeaderValues($ws, $headerRow);

            $date_field = $this->_date_field;
            $dateColNum = $this->_spreadsheetCols[$date_field];
            $line_item_field = $this->_line_item_field;
            $lineItemColNum = $this->_spreadsheetCols[$line_item_field];
            $device_field = $this->_device_field;
            $deviceColNum = $this->_spreadsheetCols[$device_field];
            $impressions_field = $this->_impressions_field;
            $impressionsColNum = $this->_spreadsheetCols[$impressions_field];
            $gross_revenue_field = $this->_gross_revenue_field;
            $grossRevenueColNum = $this->_spreadsheetCols[$gross_revenue_field];

            $status = TRUE;

            $curRow = $headerRow + 1;
            while ($status) {
                $date = $ws->getCellByColumnAndRow($dateColNum, $curRow)->getFormattedValue();
                if (!$date) {
                    $status = FALSE;
                } else {
                    $obj = [];
                    $obj[$date_field] = $date;
                    $obj[$line_item_field] = $ws->getCellByColumnAndRow($lineItemColNum, $curRow)->getFormattedValue();
                    $obj[$device_field] = $ws->getCellByColumnAndRow($deviceColNum, $curRow)->getFormattedValue();

                    $obj[$impressions_field] = $ws->getCellByColumnAndRow($impressionsColNum, $curRow)->getValue();
                    $obj[$gross_revenue_field] = $ws->getCellByColumnAndRow($grossRevenueColNum, $curRow)->getValue();
                    $arr[] = $obj;
                }
                $curRow++;
            }
        }

        return $arr;
    }

    private function fillRemainingHeaderValues($ws, $headerRow) {
        $status = TRUE;
        $count = 0;
        if ($this->_remainingHeaderValues) {
            while ($status) {
                $colVal = $ws->getCellByColumnAndRow($count, $headerRow)->getFormattedValue();
                if ($colVal) {
                    if (array_key_exists($colVal, $this->_remainingHeaderValues)) {
                        $this->_spreadsheetCols[$colVal] = $count;
                    }
                } else {
                    $status = FALSE;
                }
                $count++;
            }
        }
    }
}