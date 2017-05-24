<?php

// Currently cannot get the full breakdown via the API

namespace App\Data\Source;

use Log;
use App\Data\Source;

class OpenXBase extends \App\Data\Email {
    private $isLoggedIn;
    private $_usEmailSubject;
    private $_allEmailSubject;
    private $_numberOfReportColumns;

    public function __construct($params, $usEmailSubject, $allEmailSubject, $source_id, $product_type_id, $numberOfReportColumns) {
        $this->isLoggedIn = false;
        $this->_usEmailSubject = $usEmailSubject;
        $this->_allEmailSubject = $allEmailSubject;
        $this->_numberOfReportColumns = $numberOfReportColumns;

        $params['source_id'] = $source_id;
        $params['product_type_id'] = $product_type_id;

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

        $reports = $this->importAllAndUSFromMailbox();
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

        $source = $this->source();
        $date_field = $source->date_field;
        $dateSet = $this->getMinAndMaxDates($date_field, "m/d/Y", $usReport, $allReport);
        $minDate = $dateSet['min'];
        $maxDate = $dateSet['max'];
        if (!minDate || !maxDate) {
            return $this->setSourceStatus($source_id, 'Server Error', 8, 'OpenX: cannot parse dates in csv');

        }

        $this->clearSourceMetrics($source_id, ['start' => $minDate, 'end' => $maxDate]);

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

        if ($this->doClearInbox()) {
            $this->moveEmailsOutOfInbox();
        }
        $this->refreshMetrics(['start' => $minDate, 'end' => $maxDate]);
        return true;
    }

    public function processDownload($geo, $report, &$badPublisherIds) {
        $source = $this->source();
        $source_id = $this->source_id();
        $product_type_id = $this->product_type_id();
        $dateLookup = $this->dateLookup();
        $toDb = [];
        foreach ($report as $item) {
            $extracted = $this->extractRow($item, $geo, $source, $dateLookup);
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
        $standardParse = $this->matchStandardDisplayLineItem($s);
        if ($standardParse) {
            $parsed = $standardParse;
        } else {
            $parsed = $this->matchNonStandardLineItem($s);
        }
        return $parsed;
    }



    private function importFromMailbox($subject) {

        $source_id = $this->source_id();
        $searchResult = $this->getMailBySubject($subject);
        if (!searchResult) {
            $this->setSourceStatus($source_id, 'Server Error', 10, $subject);
            return null;
        }

        $mail = $searchResult['mail'];
        $mailNumber = $searchResult['mailNumber'];
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
            $csvData = file_get_contents($filePath);
        } catch (\Exception $e) {
            unlink($filePath);
            $this->setSourceStatus($source_id, 'Server Error', 13, $subject);
            return null;
        }
        
        unlink($filePath);
        if (!$csvData) {
            $this->setSourceStatus($source_id, 'Server Error', 13, $subject);
            return null;
        }
        $import = $this->csvToAssocWithHeaderFind($csvData, "Site Name", $this->_numberOfReportColumns);
        $this->addToImported($mailNumber);
        return $import;
    }


    private function importAllAndUSFromMailbox() {
        $usImport = $this->importFromMailbox($this->_usEmailSubject);
        if ($usImport) {
            $allImport = $this->importFromMailbox($this->_allEmailSubject);
            if ($allImport) {
                return ['us' => $usImport, 'all' => $allImport];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function mapDevice($input) {
        $result = null;
        $inputLC = strtolower($input);
        if ($inputLC === "connected device" ) {
            $result = "mob";
        } else if ($inputLC === "desktop") {
            $result = "dsk";
        } else if ($inputLC === "phone") {
            $result = "mob";
        } else if ($inputLC === "tablet") {
            $result = "mob";
        } else if ($inputLC === "device") {
            $result = "mob";
        }
        return $result;
    }

    protected function _formatDate($date){
        $date = date("Y-m-d", strtotime($date));
        return $date;
    }

    protected function _formatDateTime($date){
        return $date. ' 00:00:00';
    }
}