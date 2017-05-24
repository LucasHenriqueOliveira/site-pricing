<?php

// Currently cannot get the full breakdown via the API

// Sharethrough's email report does not provide a date, so we use the date of the email in the America/New_York time zone
// No ad size (though maybe possible)
// No slot (though maybe possible)
namespace App\Data\Source;

use Log;
use App\Data\Source;

class Sharethrough extends \App\Data\Email {
    private $isLoggedIn;
    private $_subject;

    // We are doing this here instead of in the database because it is easier to change with the column validators above
    private $_line_item_field = "Placement";
    private $_publisher_name_field = "Site / App";
    private $_geo_field = "Country";
    private $_impressions_field = "Visible Impressions (monetize)";
    private $_gross_revenue_field = "Pub Earnings (monetize)";

    protected $_spreadsheetCols;

    public function __construct($params = []) {
        $this->isLoggedIn = false;

        $this->_subject = "Sharethrough Custom Report - The PublisherDesk - Daily Programmatic Performance";

        $params['source_id'] = 21;
        $params['product_type_id'] = 3; // Native

        $this->_spreadsheetCols = [
            "Publisher" => 0,
            $this->_publisher_name_field => 1,
            $this->_line_item_field => 2,
            $this->_geo_field => 3,
            "Impressions (monetize)" => 4,
            $this->_impressions_field => 5,
            "Engagements - total (monetize)" => 6,
            $this->_gross_revenue_field => 7
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

        $imported = $this->importFromMailbox();
        $report = $imported["report"];
        $date = $imported["date"];

        if(!$date) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No date available');
        }

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        $reportCheck = $this->checkImportedDataLength($report);
        if(!$reportCheck) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

//        $source = $this->source();

        $this->clearSourceMetrics($source_id, ['start' => $date, 'end' => $date]);

        $badPublisherIds = [];
        $consolidated = $this->processDownload($report, $date, $badPublisherIds);

        $this->logUnrecognizedImportedPublishers($badPublisherIds, $source_id);

        if ($consolidated && $writeToDb) {
            $this->writeRowsToMetricBySourceAndFullSplitDaily($consolidated);
        }

        if ($this->doClearInbox()) {
            $this->moveEmailsOutOfInbox();
        }
        $this->refreshMetrics(['start' => $date, 'end' => $date]);
        return true;
    }

    public function processDownload($report, $date, &$badPublisherIds) {
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
                    $extracted['date'] = $this->_formatDateTime($date);
                    $toDb[] = $extracted;
                }
            }
        }

        $consolidated = $this->consolidateDataRows($toDb);
        $consolidated = $this->removeBadPublisherIds($consolidated, $badPublisherIds);
        return $consolidated;
    }

    public function extractRow($import) {
        // If the row is blank, we don't want to output an error
        $extracted = null;

        $line_item_field = $this->_line_item_field;
        $publisher_name_field = $this->_publisher_name_field;
        $geo_field = $this->_geo_field;
        $impressions_field = $this->_impressions_field;
        $gross_revenue_field = $this->_gross_revenue_field;

        if ($import) {
            // See if we can get data out of the line item
            $line_item = $import[$line_item_field];
            if ($line_item) {
                $parsed = $this->parseLineItem($line_item);
                if (!$parsed || !$parsed["publisher_id"]) {
                    $publisher_name = $import[$publisher_name_field];
                    $publisher_id = $this->getPublisherIdFromPublisherName($publisher_name);
                    if (!$publisher_id) {
                        // Try a compressed name match
                        $name2 = strtolower(preg_replace('/\s/', '', $publisher_name));
                        $publisher_id = $this->getPublisherIdFromPublisherName($name2);
                    }
                    if ($publisher_id) {
                        $parsed["publisher_id"] = $publisher_id;
                    } else {
                        Log::error("Don't recognize Sharethrough publisher: ".$publisher_name);
                        $extracted["error"] = ["code" => 6, "info" => $publisher_name." ".$line_item];
                        return $extracted;
                    }
                }
                $extracted = [];
                $extracted["date"] = $this->_formatDateTime($tempDate);
                $extracted["publisher_id"] = $parsed["publisher_id"];
                $gross_revenue = Source::extractNumber($import[$gross_revenue_field]);
                // search client_fraction in revenue_share table
                $revenue_share = $this->getRevenueShare($extracted['publisher_id']);
                $extracted['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;
                $extracted['gross_revenue'] = $gross_revenue;

                $extracted["impressions"] = Source::extractNumber($import[$impressions_field]);

                $geo = Sharethrough::mapGeo($import[$geo_field]);
                $extracted["geo"] = $this->chooseFromThree($geo, $parsed["geo"], "n/a");

                // Note that we are not extracting slot or device or ad size from the line item for now, though this could be added later
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
        // We know the Sharethrough line items are not standard, so don't bother logging those errors for now
//        Log::error("Can't parse Sharethrough line item: ".$s);
//        echo $s."\n";
        return $parsed;
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
        $dt = new \DateTime($mail->date, new \DateTimeZone('UTC'));
        $dt->setTimezone(new \DateTimeZone('America/New_York'));
        // Emails are sent a day later, so we need to subtract a day off
        $date = $dt->modify('-1 day')->format('Y-m-d');
        Log::info("Retrieving Sharethrough email for date: ".$date);

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

        if (!$spreadsheet) {
            $this->setSourceStatus($source_id, 'Server Error', 13, $subject);
            return null;
        }
        $ws = $spreadsheet->getSheet(0);

        $import = $this->worksheetToArray($ws);
        $this->addToImported($mailNumber);
        return ["report" => $import, "date" => $date];
    }

    private function worksheetToArray($ws) {
        $source_id = $this->source_id();
        if ($ws) {
            $headerRow = 3;
            $badHeaders = $this->checkSpreadsheetBadHeaders($ws, $headerRow);
            if ($badHeaders) {
                $badHeadersString = implode($badHeaders, ", ");
                $this->setSourceStatus($source_id, 'Server Error', 14, "Bad headers: ".$badHeadersString);
                return null;
            }

            $geo_field = $this->_geo_field;
            $geoColNum = $this->_spreadsheetCols[$geo_field];
            $line_item_field = $this->_line_item_field;
            $lineItemColNum = $this->_spreadsheetCols[$line_item_field];
            $publisher_name_field = $this->_publisher_name_field;
            $publisherNameColNum = $this->_spreadsheetCols[$publisher_name_field];
            $impressions_field = $this->_impressions_field;
            $impressionsColNum = $this->_spreadsheetCols[$impressions_field];
            $gross_revenue_field = $this->_gross_revenue_field;
            $grossRevenueColNum = $this->_spreadsheetCols[$gross_revenue_field];

            $status = TRUE;

            // Sharethrough cells are merged vertically in some cases, which means that only the top cell has a non-null value
            $curPublisherName = null;
            $curLineItem = null;
            $curRow = $headerRow + 1;
            while ($status) {
                $geo = $ws->getCellByColumnAndRow($geoColNum, $curRow)->getFormattedValue();
                if (!$geo) {
                    $status = FALSE;
                } else {
                    $obj = [];
                    $obj[$geo_field] = $geo;
                    $lineItem = $ws->getCellByColumnAndRow($lineItemColNum, $curRow)->getFormattedValue();
                    if (!$lineItem) {
                        $lineItem = $curLineItem;
                    } else {
                        $curLineItem = $lineItem;
                    }
                    $obj[$line_item_field] = $lineItem;

                    $publisherName = $ws->getCellByColumnAndRow($publisherNameColNum, $curRow)->getFormattedValue();
                    if (!$publisherName) {
                        $publisherName = $curPublisherName;
                    } else {
                        $curPublisherName = $publisherName;
                    }
                    $obj[$publisher_name_field] = $publisherName;

                    $obj[$impressions_field] = $ws->getCellByColumnAndRow($impressionsColNum, $curRow)->getValue();
                    $obj[$gross_revenue_field] = $ws->getCellByColumnAndRow($grossRevenueColNum, $curRow)->getValue();
                    $arr[] = $obj;
                }
                $curRow++;
            }
        }

        return $arr;
    }

    public static function mapGeo($input) {
        $retval = NULL;
        $input_lc = strtolower($input);
        if ($input_lc === "us") {
            $retval = "us";
        } else {
            $retval = "in";
        }
        return $retval;
    }

    private function _formatDateTime($date){
        return $date. ' 00:00:00';
    }
}