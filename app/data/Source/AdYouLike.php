<?php

namespace App\Data\Source;

use Illuminate\Support\Facades\DB;
use Log;
use App\Data\Source;

class AdYouLike extends \App\Data\Scrape {
    private $_accessToken;

    public function __construct($params = []) {
        $params['source_id'] = 7;
        $params['product_type_id'] = 3; // Native
        parent::__construct($params);
    }

    // ================================================
    public function login() {
        $res = $this->client()->request('POST', 'https://bo-api.omnitagjs.com/bo-api/auth/login', [
            'json' => [
                'AdminHostname' => 'admin.adyoulike.com',
                'Email' => $this->username(),
                'Password' => $this->password(),
                'Realm' => '6ab7dcb6c56af1167efbbe6c11d54b90'
            ]
        ]);

        $data = json_decode($res->getBody()->getContents());
        if (!$data->Token) {
            return false;
        }
        $this->_accessToken = $data->Token;

        return true;
    }

    // ================================================
    // AdYouLike::harvestSingleDay()
    // Requests a .csv file report from the AdYouLike (omnitagjs.com) servers for the date specified
    // in the arguments.  Parses that file and returns the data inside as an associative array.
    //
    // Arguments are passed as elements in the $params array.
    // $params[] =
    //      'date' = 'YYYY-MM-DD'
    //      'geo'  = 'ALL' OR 'us'
    //
    // Notes:
    //      1) This function does not take a range of dates.
    public function harvestSingleDay($params) {
        // Many attempts have been made to specify a range of dates here, but all publications records
        // returned are always timestamped with the start date, so there's no point.
        $begin = new \DateTime($params['date']);
        $end = new \DateTime($params['date']);
        $end = $end->modify( '+1 day' );

        $reportParams =
            [
                'Intervals' => [[
                    'Begin' => $this->formatDateTime($begin),
                    'End' => $this->formatDateTime($end)
                ]],
                'OrderBy' => 'PricePublisher',
                'OrderOp' => 'DESC',
                'Splits' => [
                    'Site',
                    'Device'
                    // Unable to split by 'Day' since this triggers a lethal server error.
                ],
                'Size' => 10000,
                'Columns' => [
                    'Name',
                    'PricePublisher',
                    'INSERTION'
                ],
                'View' => 'SIMPLE_PUBLISHER',
                'AddTotalRow' => true,
                'TimeZone' => 'America/Los_Angeles',
                'Granularity' => 'all',
            ];

            if($params['geo'] === 'us') {
                $reportParams['Filters'] = [
                        'Country' => [
                            'Value' => ['US'],
                        ]
                    ];
                // print_r($reportParams);
            }

        $res = $this->client()->request('POST', 'https://bo-api.omnitagjs.com/bo-api/druid/search', [
            'json' => $reportParams,
            'headers' => [
                // 'Referer' => 'https://admin.adyoulike.com/uk/',
                // 'Content-Type' => 'application/json;charset=UTF-8',
                'X-AYL-Auth-Token' => $this->accessToken()
            ]
        ]);
        $data = json_decode($res->getBody()->getContents())->Data;
        return $data;
    }

    // ================================================
    // AdYouLIke::ingestSingleDay()
    // Requests and parses multiple .csv files from the AdYouLike (omnitagjs.com) servers, performs
    // some calculations, then inserts the resulting publication revenue records in the database,
    // logging errors if these steps fail.
    //
    // Arguments are passed as elements in the $params array.
    // $params[] =
    //      'date'   = 'YYYY-MM-DD'
    // Notes:
    //      This function accepts a single date, rather than a range of dates.
    public function ingestSingleDay($params = []) {
        ini_set('max_execution_time', 180);
        $source_id = $this->source_id();
        $db_success = [];

        // attempt to present login credentials
        try {
            $login = $this->login();
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
        }

        // attempt to harvest US-only .csv data
        try {
            $reportUS = $this->harvestSingleDay([
                'date' => $params['date'],
                'geo' => 'us'
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }
        if(!$reportUS) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        // attempt to harvest ALL-nations .csv data
        try {
            $reportALL = $this->harvestSingleDay([
                'date' => $params['date'],
                'geo' => 'ALL'
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }
        if(!$reportALL) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        return $this->insertInDatabase($this->aggregateOnDevices($this->serializeAndDigest(
            $this->computeInternational($this->collateRecords($reportUS, $reportALL)))));
    }

    // ================================================
    // AdYouLike::import()
    // import is the standard function all source ingestion code is required to implement.  Expected to accept a range
    // of dates as arguments.
    //
    // Arguments are passed as elements in the $params array.
    // $params[] =
    //      'start' = 'YYYY-MM-DD'
    //      'end'   = 'YYYY-MM-DD'
    // Notes:
    //      Implemented as a mere wrapper around AdYouLike::ingestSingleDay()
    public function import($params) {
        // loop over all the days in the interval and call ingestSingleDay() for each one.
        // http://php.net/manual/en/class.dateperiod.php
        $db_success = [];
        $begin = new \DateTime($params['start']);
        $end = new \DateTime($params['end']);
        $end = $end->modify( '+1 day' );

        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval ,$end);

        foreach($daterange as $date) {
            $ingestPar = [];
            $ingestPar['date'] = $date->format("Y-m-d");
            array_push($db_success, $this->ingestSingleDay($ingestPar));
        }

        $this->refreshMetrics($params);
        // http://stackoverflow.com/questions/10314389/shortest-way-to-evaluate-an-array-with-booleans-php
        return(!(in_array(false, $db_success)));

    }

    // ================================================
    // AdYouLike::insertInDatabase()
    // Takes a report as an argument and inserts the publication records into the database.
    private function insertInDatabase($report) {
        $db_success = [];
        foreach($report as $record) {
            // We really want an 'upsert' operation. We can't be sure we haven't run this ingestion of this
            // same data before.  Therefore we should simply replace it rather than add it again if this
            // is a repeat.
            // https://chartio.com/resources/tutorials/how-to-insert-if-row-does-not-exist-upsert-in-mysql/
            $keys = implode(', ', array_keys($record));
            $values = "'" .implode("','", array_values($record)) . "'";
            $DIRECTIVE = 'REPLACE INTO metric_by_source_and_full_split_daily('.$keys.') VALUES ('.$values.')';
            array_push($db_success, DB::insert($DIRECTIVE, $record));
        }
        // http://stackoverflow.com/questions/10314389/shortest-way-to-evaluate-an-array-with-booleans-php
        return(!(in_array(false, $db_success)));
    }

    // ================================================
    // AdYouLike::aggregateOnDevices()
    // Takes a serialized (flat) array of records, aggregates all non-desktop devices together,
    // and returns another flat array of records.  Aggregation does not span publisher or geo.
    // This results in only two classes of device, desktop, and everything else, classified as
    // mobile.  Also...
    //   - Renames desktop -> dsk and mobile -> mob
    //
    // Warning:  Aggregation does span date, which you probably did not want.  This code is
    // written to be run on single day data harvests only where this is not an issue.  Feel
    // free to improve it if you need to prevent aggregation on date.
    private function aggregateOnDevices($flatReport) {
        $binReport = [];
        // $binReport (binned reports) is a multi-dimensional intermediate array used to organize
        // the records before aggregation.  It is to be indexed as $binReport[publisher_id][geo]
        // and each element is an array of integers, each the index of the associated record in
        // the $flatReport.
        foreach($flatReport as $i => $record) {
            if(!$binReport[$flatReport[$i]['publisher_id']]) {
                $binReport[$flatReport[$i]['publisher_id']] = [];
            }
            if(array_key_exists($flatReport[$i]['geo'], $binReport[$flatReport[$i]['publisher_id']])) {
                array_push($binReport[$flatReport[$i]['publisher_id']][$flatReport[$i]['geo']], $i);
            } else {
                $binReport[$flatReport[$i]['publisher_id']][$flatReport[$i]['geo']] = [$i];
            }
        }
        $result = [];
        foreach($binReport as $i => $pubReport) {
            foreach($binReport[$i] as $j => $geoReport) {
                // first pass:  copy and remove all DESKTOP entries
                foreach($binReport[$i][$j] as $k => $recNum) {
                    $temp = $flatReport[$binReport[$i][$j][$k]];
                    if ($temp['device'] === 'DESKTOP') {
                        // There should only be a single DESKTOP entry in this list.
                        // @TODO Add a check to verify that only a single DESKTOP entry is found.
                        // However, for the second pass to work, no DESKTOP records can remain in
                        // the list.  They must all be removed.
                        $temp['device'] = 'dsk';
                        array_push($result, $temp);
                        // http://stackoverflow.com/questions/2304570/how-to-delete-object-from-array-inside-foreach-loop
                        unset($binReport[$i][$j][$k]);
                    }
                }
                // second pass: (assuming there's any left)
                //   These are all non-DESKTOP entries now
                if($binReport[$i][$j]) {
                    $temp = $flatReport[$binReport[$i][$j][0]];
                    unset($binReport[$i][$j][0]);
                    foreach($binReport[$i][$j] as $k => $recNum) {
                        $temp['page_views'] += $flatReport[$recNum]['page_views'];
                        $temp['net_revenue'] += $flatReport[$recNum]['net_revenue'];
                        $temp['gross_revenue'] += $flatReport[$recNum]['gross_revenue'];
                    }
                    $temp['device'] = 'mob';
                    array_push($result, $temp);
                }
            }
        }
        return($result);
    }

    // ================================================
    // AdYouLike::serializeAndDigest()
    // Takes collated US and IN records and returns them as a flat array suitable for
    // inserting into the database.  Also...
    //   - Relabels all fields with our rationalized in-house scheme
    //   - Filters out all records with zero revenue
    //   - Represents revenue in dollars by dividing out AdYouLike's implied multiplier of
    //       10^6
    private function serializeAndDigest($collReport) {
        $source_id = $this->source_id();
        $product_type_id = $this->product_type_id();
        $flatReport = [];
        foreach($collReport as $i => $collRecord) {
            $targets = ['linkUS', 'IN'];
            foreach($targets as $target) {
                if(array_key_exists($target, $collReport[$i])) {
                    if(intval($collReport[$i][$target]->PricePublisher) > 0) {
                        if(!($publisher_id = $this->identifyPublisher($collReport[$i][$target]->Name_Site))) {
                            $this->setSourceStatus($source_id, 'Server Error', 6,
                                'Publication report for unlisted publisher, '
                                .$this->regularizeName($collReport[$i][$target]->Name_Site).'.');
                        } else {
                            // obtain client_fraction from revenue_share table
                            $revenue_share = $this->getRevenueShare($publisher_id);
                            if($target === 'linkUS') {
                                $geo = 'us';
                            } else if($target === 'IN') {
                                $geo = 'in';
                            } else {
                                // this should never happen
                                $geo = NULL;
                                $this->setSourceStatus($source_id, 'Server Error', 9,
                                'Invalid geo detected in parsing pipeline.');
                            }
                            array_push($flatReport,
                                [
                                    'geo' => $geo,
                                    'page_views' => $collReport[$i][$target]->INSERTION,
                                    'date' => $collReport[$i][$target]->timestamp,
                                    'product_type_id' => $product_type_id,
                                    'slot' => 'Native-ad', // @TODO enter correct slot for AdYouLike
                                    'source_id' => $source_id,
                                    'ad_size' => NULL,
                                    'device' => $collReport[$i][$target]->Device,
                                    'publisher_id' => $publisher_id,
                                    'net_revenue' => (float)$revenue_share->client_fraction
                                        * $collReport[$i][$target]->PricePublisher/1.E6,
                                    'gross_revenue' => $collReport[$i][$target]->PricePublisher/1.E6

                                ]
                            );
                        }
                    }
                }
            }
        }
        return($flatReport);
    }

    // ================================================
    // AdYouLike::identifyPublisher()
    // Takes the Name_Site field from an AdYouLike record and identifies our corresponding
    // assigned publisher_id.
    private function identifyPublisher($pubURL) {
        $pubName = $this->regularizeName($pubURL);

        // grab list of publishers and ids
        $ps = $this->publishers();

        // compare each in turn looking for a match
        foreach($ps as $p) {
            if(($pubName === $p->shortName) || ($pubName === $p->name) ||
             ($pubName === $p->altName) || ($pubName === $p->siteNameLC)) {
                $retval = $p->publisher_id;
                break;
            }
        }
        return $retval;
    }

    // ================================================
    // AdYouLike::regularizeName()
    // AdYouLike identifies the publishers by URL complete with scheme.
    // E.g. [Name_Site] => http://www.androidauthority.com/
    // This function takes AdYouLike names in that form and strips them down to just
    // the domain, e.g. androidauthority.com
    // Note:  Removes whitespace and moves everything to lowercase.
    private function regularizeName($adyoulikeName) {
        $domain = str_ireplace('www.', '', parse_url($adyoulikeName, PHP_URL_HOST));
        // preg_replace here removes all whitespace
        return(strtolower(preg_replace('/\s/', '', $domain)));
    }



    // ================================================
    // AdYouLike::computeInternational()
    // Takes collated US and All-Nation data, and computes international as the difference,
    // All-Nation minus US-Only.  It then generates records for the international data and
    // returns an array of the US and international records only.
    private function computeInternational($collReport) {
        $source_id = $this->source_id();
        foreach($collReport as $i => $item) {
            if(array_key_exists('linkALL', $collReport[$i])) {
                if(array_key_exists('linkUS', $collReport[$i])) {
                    // If we have both records, then compute the difference and insert the
                    // result as a new record.
                    $collReport[$i]['IN'] = (object)
                        [
                            'Device' => $collReport[$i]['linkALL']->Device,
                            'INSERTION' => intval($collReport[$i]['linkALL']->INSERTION)
                                            - intval($collReport[$i]['linkUS']->INSERTION),
                            'Name_Site' => $collReport[$i]['linkALL']->Name_Site,
                            'PricePublisher' => intval($collReport[$i]['linkALL']->PricePublisher)
                                                - intval($collReport[$i]['linkUS']->PricePublisher),
                            'timestamp' => $collReport[$i]['linkALL']->timestamp
                        ];
                } else {
                    // If the US record is missing, then return the All-Nations data as the
                    // new international record.
                    $collReport[$i]['IN'] = (object)
                        [
                            'Device' => $collReport[$i]['linkALL']->Device,
                            'INSERTION' => intval($collReport[$i]['linkALL']->INSERTION),
                            'Name_Site' => $collReport[$i]['linkALL']->Name_Site,
                            'PricePublisher' => $collReport[$i]['linkALL']->PricePublisher,
                            'timestamp' => $collReport[$i]['linkALL']->timestamp
                        ];
                }
            } else {
                if(array_key_exists('linkUS', $collReport[$i])) {
                    return $this->setSourceStatus($source_id, 'Server Error', 8,
                        'US-Only record without matching All-Nations record shouldnt be
                        possible on AdYouLike.');
                }
            }
        }
        return $collReport;
    }

    // ================================================
    // AdYouLike::collateRecords()
    // Takes US and All-Nation report arrays and collates them, attempting to match the
    // records between the two reports by site-name and device (and implicitly date).  Returns
    // an array of all the site-names and references to the records in each report.
    //
    // This is worthwhile because, once matched, US can be subtracted from All-Nation to
    // provide the international value we wish to report.
    private function collateRecords(&$reportUS, &$reportALL) {
        $collReport = [];
        foreach($reportUS as $i => $recordUS) {
            array_push($collReport,
                [
                    // the bloody records are stdClass Objects, not arrays, so we can't index
                    // them in the usual manner.  Instead we must use this weird -> notation.
                    'name' => parse_url($reportUS[$i]->Name_Site)['host'],
                    'device' => $reportUS[$i]->Device,
                    'linkUS' => &$reportUS[$i]
                ]
            );
        }

        foreach($reportALL as $i => $recordALL) {
            $nameALL = parse_url($reportALL[$i]->Name_Site)['host'];
            $addedALL = false;
            foreach($collReport as $j => $item) {
                // if nameALL+device is already in the $collReport
                if(($nameALL === $collReport[$j]['name']) &&
                    ($reportALL[$i]->Device === $collReport[$j]['device'])) {
                    // then simply add the new ALL link alongside the existing US link.
                    $collReport[$j]['linkALL'] = &$reportALL[$i];
                    $addedALL = true;
                    break;
                }
            }

            if($addedALL == false) {
                // If it wasn't found, then add it on its own.
                array_push($collReport,
                    [
                        'name' => $nameALL,
                        'device' => $reportALL[$i]->Device,
                        'linkALL' => &$reportALL[$i]
                    ]
                );
            }
        }
        return $collReport;
    }

    // ================================================
    public function accessToken() {
        return $this->_accessToken;
    }

    // ================================================
    private function formatDate($date) {
        return (int)str_pad((new \DateTime($date))->getTimestamp(), 13, '0');
    }

    // ================================================
    private function formatDateTime($datetimeObject) {
        return (int)str_pad($datetimeObject->getTimestamp(), 13, '0');
    }

    // ================================================
    private function timestampFromDateString($dateString) {
        $dt = new \DateTime($dateString);
        return $dt->format("Y-m-d")." 00:00:00";
    }

}