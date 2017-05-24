<?php

namespace App\Data\Source;

use Illuminate\Support\Facades\DB;
use Log;
use App\Data\Source;

class Taboola extends \App\Data\Scrape {

    public function __construct($params = []) {
        $params['source_id'] = 5;
        $params['product_type_id'] = 1; // Contextual
        parent::__construct($params);
    }

    // ================================================
    // Taboola::login()
    public function login() {
        $phantom = shell_exec('cd '.__DIR__.'/../../../ && casperjs --ignore-ssl-errors=true --ssl-protocol=any '.'--cookies-file=cookies.txt js/Taboola.js --username="'.$this->username().'" --password="'.$this->password().'" 2>&1');
        $data = json_decode($phantom);

        // More often than not the following code generates the following error:
        // {"message":"Invalid argument supplied for foreach()","status_code":500}
        // After I rerun it several times it starts working better.  Timing issue?
        foreach($data as $datum) {
            $cookie = new \GuzzleHttp\Cookie\SetCookie([
                'Domain'  => $datum->domain,
                'Expires' => $datum->expires,
                'Expiry'  => $datum->expiry,
                'HttpOnly'=> $datum->httponly,
                'Name'    => $datum->name,
                'Path'    => $datum->path,
                'Secure'  => $datum->secure,
                'Value'   => empty($datum->value) ? 0 : $datum->value,
                'Discard' => false
            ]);

            $success[] = $this->jar()->setCookie($cookie);
        }
//        print_r($success);

        // Use this line if you want to skip the query to Taboola and instead the read the cookies from
        // the cookies.txt file you already have cached.
        // $this->jar = (new \GuzzleHttp\Cookie\FileCookieJar(__DIR__.'/../../../cookies.txt'));

//        var_dump($this->jar());
        return true;
    }

    // ================================================
    // Taboola::harvestSingleDay()
    // Requests a .csv file download from the backstage.taboola.com servers for the date specified
    // in the arguments.  Parses that file and returns the data inside as an associative array.
    //
    // Arguments are passed as elements in the $params argument.
    // $params[] =
    //      'date' = 'YYYY-MM-DD'
    //      'geo'  = 'ALL' OR 'us'
    //      'device' = 'desktop' OR 'other'
    //
    // Notes:
    //      This function does not take a range of dates.
    public function harvestSingleDay($params = []) {
        if($params['geo'] == 'us') {
            $geoCode = '225';
        } else {
            $geoCode = '-100';
        }
        if($params['device'] == 'desktop') {
            $deviceCode = '%221%22%2C%226%22';
        } else {
            $deviceCode = '%220%22%2C%222%22%2C%223%22%2C%224%22%2C%225%22';
        }
        $result = $this->client()->request(
            'GET',
            'https://backstage.taboola.com/backstage/transform/1054495/performance/guarantee-revenue/csv'
            .'?currentDimension=site'
            .'&marker'
            .'&id=8vt1cfrooibi'
            .'&groupId=performance'
            .'&reportId=guarantee-revenue'
            .'&dateStart='.$params['date']
            .'&dateEnd='.$params['date']
            .'&dateRangeValue=0'
            .'&term='.$params['date'].'%2000%3A00%3A00%20to%20'.$params['date'].'%2023%3A59%3A59'
            .'&queryFilter=%5B%7B%22id%22%3A%22composed_placement_page_type%22%2C%22operator%22%3A%22equal%22%2C%22value%22%3A%22-1%22%7D%2C%7B%22id%22%3A%22composed_sub_placement%22%2C%22operator%22%3A%22equal%22%2C%22value%22%3A%22-1%22%7D%2C%7B%22id%22%3A%22country_filter%22%2C%22operator%22%3A%22equal%22%2C%22value%22%3A%22'.$geoCode.'%22%7D%2C%7B%22id%22%3A%22platform_filter%22%2C%22operator%22%3A%22equal%22%2C%22value%22%3A%5B'.$deviceCode.'%5D%7D%5D',
            [
                'cookies' => $this->jar(),
                'debug' => false,
                'headers' => [
                    'Referer' => 'https://backstage.taboola.com/backstage/1054495/reports/performance/guarantee-revenue',
                    'User-Agent' => $this->agent()
                ]
            ]
        );
        $csvData = $result->getBody()->getContents();
        // The data returned by this function is prepended with a BOM (byte order mark) character, which needs to
        // be removed.  This is what mb_substr() is doing here.
        return($this->csvToAssoc(mb_substr($csvData, 1, NULL, 'UTF-8')));
    }



    // ================================================
    // Taboola::ingestSingleDay()
    // Requests and parses multiple .csv files from backstage.taboola servers, performs some calculations, then inserts
    // the resulting publication revenue records in the database, logging errors if these steps fail.
    //
    // Arguments are passed as elements in the $params array.
    // $params[] =
    //      'date'   = 'YYYY-MM-DD'
    //      'device' = 'desktop' OR 'other'
    // Notes:
    //      This function accepts a single date, rather than a range of dates.
    public function ingestSingleDay($params = []) {
        ini_set('max_execution_time', 180);
        $source_id = $this->source_id();
        $db_success = [];

        // attempt to harvest US-only .csv data
        try {
            $reportUS = $this->harvestSingleDay([
                'date' => $params['date'],
                'geo' => 'us',
                'device' => $params['device']
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
                'geo' => 'ALL',
                'device' => $params['device']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }
        if(!$reportALL) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        // Identify each of the all-nations records with a US-only record (by reference)
        foreach($reportALL as &$recordALL) {
            foreach($reportUS as &$recordUS) {
                if($recordALL['Publisher'] === $recordUS['Publisher']) {
                    $recordALL['usLink'] = &$recordUS;
                    $recordUS['allLink'] = &$recordALL;
                    break;
                }
            }
        }
        // https://coderwall.com/p/qx3fpa/php-foreach-pass-by-reference-do-it-right-or-better-not-at-all
        unset($recordALL);
        unset($recordUS);
        // @TODO - I forgot that if we have no US-only record to subtract off then we simply subtract
        // off zero.  Crud.  I've got to rewrite this code to get rid of the US-only partner record
        // mandate.

        $product_type_id = $this->product_type_id();

        // insert publication reports in to database
        foreach($reportUS as $recordUS) {
            // If you can't get the publisher_id you cannot insert the publication record
            // and instead we just report the error
            if(!($usData['publisher_id'] = $this->identifyPublisher($recordUS['Publisher']))) {
                $this->setSourceStatus($source_id, 'Server Error', 6,
                    'Publication report for unlisted publisher, '
                    .strtolower(preg_replace('/\s/', '', explode('-', $recordUS['Publisher'])[1])));
            } else {
                // Taboola reports are for a single date thus dates are not explicitly included in the
                // publication records.  No problem, we can just get the date from the request that
                // generated the report in the first place.
                $usData['date'] = $this->formatDateTime($params['date']);
                $usData['product_type_id'] = $product_type_id;
                $usData['slot'] = 'contextual-ad-a';
                $usData['source_id'] = $source_id;
                if($params['device'] === 'desktop') {
                    $usData['device'] = 'dsk';
                } else {
                    $usData['device'] = 'mob';
                }

                $usData['geo'] = 'us';
                $usData['page_views'] = Source::extractNumber($recordUS['Views with Ads']);

                // search client_fraction in revenue_share table
                $revenue_share = $this->getRevenueShare($usData['publisher_id']);
                // 'Ad Revenue' returns the value as a string prepended with a '$' so I have to parse it
                // with float_val() and trim() to get an operable number.
                $gross_revenue = Source::extractNumber(ltrim($recordUS['Ad Revenue'], '$'));
                $usData['gross_revenue'] = $gross_revenue;
                $usData['net_revenue'] = $gross_revenue * (float)$revenue_share->client_fraction;

                // We really want an 'upsert' operation. We can't be sure we haven't run this ingestion of this
                // same data before.  Therefore we should simply replace it rather than add it again if this
                // is a repeat.
                // https://chartio.com/resources/tutorials/how-to-insert-if-row-does-not-exist-upsert-in-mysql/
                $keys = implode(', ', array_keys($usData));
                $values = "'" .implode("','", array_values($usData)) . "'";
                $DIRECTIVE = 'REPLACE INTO metric_by_source_and_full_split_daily('.$keys.') VALUES ('.$values.')';
                array_push($db_success, DB::insert($DIRECTIVE, $usData));

                // If we have a ALL-nations partner record, then insert the computed international publication
                // record.
                if($recordUS['allLink'] != NULL) {
                    $inData['publisher_id'] = $usData['publisher_id'];
                    $inData['date'] = $usData['date'];
                    $inData['product_type_id'] = $usData['product_type_id'];
                    $inData['slot'] = $usData['slot'];
                    $inData['source_id'] = $usData['source_id'];
                    $inData['device'] = $usData['device'];

                    $inData['geo'] = 'in';
                    $recordALL = $recordUS['allLink'];
                    $inData['page_views'] = Source::extractNumber($recordALL['Views with Ads']) - Source::extractNumber($recordUS['Views with Ads']);
                    $rev_gross_all = Source::extractNumber(ltrim($recordALL['Ad Revenue'], '$'));
                    $rev_net_all = $rev_gross_all * (float)$revenue_share->client_fraction;
                    $inData['gross_revenue'] = $rev_gross_all - $usData['gross_revenue'];
                    $inData['net_revenue'] = $rev_net_all - $usData['net_revenue'];

                    $keys = implode(', ', array_keys($inData));
                    $values = "'" .implode("','", array_values($inData)) . "'";
                    $DIRECTIVE = 'REPLACE INTO metric_by_source_and_full_split_daily('.$keys.') VALUES ('.$values.')';
                    array_push($db_success, DB::insert($DIRECTIVE, $inData));
                }
            }
        }

        return true;
    }

    // ================================================
    // Taboola::import()
    // Standard function ingestion code is required to implement.  Only single date accepted
    //
    // Arguments are passed as elements in the $params argument.
    // $params[] =
    //      'date' = 'YYYY-MM-DD'
    public function import($params = []) {
        $source_id = $this->source_id();

        // @TODO - We should only need to login once
        // attempt to present login credentials
        try {
            $login = $this->login();
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
        }

        // @TODO - Is this the best place to clear out the table?
        // We need to do this before the two ingestions
        $this->clearSourceMetrics($source_id, ["start" => $params['date'], "end" => $params["date"]]);

        $ingestPar['date'] = $params['date'];
        // call ingestSingleDay() twice for each day, once for 'desktop' and once for 'other'
        $ingestPar['device'] = 'desktop';
        $this->ingestSingleDay($ingestPar);
        $ingestPar['device'] = 'other';
        $this->ingestSingleDay($ingestPar);

        $this->refreshMetrics(["start" => $params['date'], "end" => $params["date"]]);

        return true;
    }

    // ================================================
    private function identifyPublisher($pubField) {
        // Taboola constructs the 'Publisher' field as a hyphenated under-specified hybrid,
        // e.g. 'The Publisher Desk - Android Authority''
        // as opposed to 'androidauthority.com', which is more common.  The following code strips
        // that back to 'androidauthority'.
        //      preg_replace here removes all whitespace
        $pubFieldLC = strtolower(preg_replace('/\s/', '', explode('-', $pubField)[1]));

        if($pubFieldLC === 'dupontregistry') {
            // @TODO:  We should consider modifying the string and forwarding the query for lookup
            // below rather than hardcoding the number as I'm doing for these two special cases.
            $retval = 5;
        } else if($pubFieldLC === 'mixedmartialarts') {
            $retval = 101;
        } else {
            // grab list of publishers and ids
            $ps = $this->publishers();

            // compare each in turn looking for a match
            foreach($ps as $p) {
                if(($pubFieldLC === $p->shortName) || ($pubFieldLC === $p->name) ||
                ($pubFieldLC === $p->altName) || ($pubFieldLC === $p->siteNameLC)) {
                    $retval = $p->publisher_id;
                    break;
                }
            }
        }
        return $retval;
    }

    // ================================================
    private function formatDateTime($monthLeadingSlashDelim) {
        $dt = new \DateTime($monthLeadingSlashDelim);
        return $dt->format("Y-m-d")." 00:00:00";
    }

}
