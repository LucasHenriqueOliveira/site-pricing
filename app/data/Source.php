<?php

namespace App\Data;

use Illuminate\Support\Facades\DB;
use \Datetime;

class Source {
    private $_username;
    private $_password;
    private $_client;
    private $_publishers;
    private $_dateLookup;

    private $_source_id;
    private $_source;

    private $_product_type_id;

    private $_displayLineItemRe = '/^([0-9]{4,6})-.*-(mob|dsk|app)-(banner|box|sky)-(ad)-([a-d])-(us|in)(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_nativeLineItemRe = '/^([0-9]{4,6})-.*-(native-ad-[a-d])-(us|in)(\s*(?:\()([0-9]+x[0-9]+)(?:\)))?/i';
    private $_shortNameRe = '/(.*)\.[A-Z]+/i';

    public function __construct($params = []) {

        $this->_source_id = $params['source_id'];
        $this->_product_type_id = $params['product_type_id'];
        $this->_client = new \GuzzleHttp\Client;

        if (array_key_exists('username', $params)) {
            $this->_username = $params['username'];
        }

        if (array_key_exists('password', $params)) {
            $this->_password = $params['password'];
        }
    }

// I didn't add this function here, but I did find something very similar on stackexchange.
// http://stackoverflow.com/questions/4801895/csv-to-associative-array
//
// Second, $skip, argument allows dropping leading rows.

    public function csvToAssoc($data, $skip = 0, $convertKeys = false, $line_feed = "\n") {
        if (!is_array($data)) {
            $rows = array_map('str_getcsv', explode($line_feed, $data));
        } else {
            $rows = $data;
        }

        for($i=0; $i<$skip; $i++) {
            // pop the first element of $rows
            array_shift($rows);
        }
        $header = array_shift($rows);
        if ($convertKeys) {
            $header = array_values($header);
        }
        $csv = [];

        foreach($rows as $row) {
            if ($convertKeys) {
                $row = array_values($row);
            }
            $line = [];
            for ($x=0; $x < count($row); $x++) {
                if (!$header[$x]) {
                    continue;
                }
                $line[$header[$x]] = $row[$x];
            }
            $csv[] = $line;
        }
        return $csv;
    }

    public function csvToAssocWithHeaderFind($data, $firstColumnValue, $numberOfColumns, $convertKeys = false, $line_feed = "\n") {
        if (!is_array($data)) {
            $rows = array_map('str_getcsv', explode($line_feed, $data));
        } else {
            $rows = $data;
        }

        $status = TRUE;
        while ($status) {
            $row = array_shift($rows);
            if ($row) {
                $vals = array_values($row);
                if ($vals && count($vals) == $numberOfColumns && $vals[0] == $firstColumnValue) {
                    if ($convertKeys) {
                        $header = $vals;

                    } else {
                        $header = $row;
                    }
                    $status = FALSE;
                }
            } else if (count($rows) == 0) {
                return null;
            }
        }
        $csv = [];

        foreach($rows as $row) {
            if ($convertKeys) {
                $row = array_values($row);
            }
            $line = [];
            for ($x=0; $x < count($row); $x++) {
                if (!$header[$x]) {
                    continue;
                }
                $line[$header[$x]] = $row[$x];
            }
            $csv[] = $line;
        }
        return $csv;
    }


    public function checkImportedDataLength($data) {
        // @TODO - add check data length
        return true;
    }

    public static function getSourceFromDb($source_id) {
        // @TODO - Cache this - similar to how getLineItem / searchForLineItem work
        return DB::select("SELECT * FROM source WHERE `source_id` = :source_id LIMIT 1", ['source_id' => $source_id])[0];
    }

    public function getRevenueShare($publisher_id) {
        $revenues = $this->revenues();
        return $revenues[$publisher_id];
    }

    public function getPublisher($publisher_id) {
        // @TODO - Cache this - similar to how getLineItem / searchForLineItem work
        return DB::select("SELECT * FROM `publisher` WHERE `publisher_id` = :publisher_id LIMIT 1", ['publisher_id' => $publisher_id])[0];
    }

    public function getPublisherFromName($name) {
        $publishers = $this->publishers();
        $nameLC = strtolower($name);
        $nameRe = '/'.$nameLC.'/i';
        $altName = $this->_getAltName($nameLC);
        foreach ($publishers as $id => $info) {

            if (preg_match($nameRe, $info->site_code) || ($nameLC === $info->shortName)) {
                return $info;
            } else if (($altName === $info->shortName) || ($altName === $info->name) || ($altName === $info->altName) || ($altName === $info->siteNameLC)) {
                return $info;
            }
        }

        return null;
    }

    public function publishers() {
        if (!$this->_publishers) {
            $this->_publishers = $this->_getPublishers();
        }
        return $this->_publishers;
    }

    private function revenues(){
        if (!$this->_revenues) {
            $this->_revenues = $this->_getRevenues();
        }
        return $this->_revenues;
    }

    public function getLineItem($source_id) {
        $line_item = [];
        $result = DB::select("SELECT * FROM line_item WHERE `source_id` = :source_id", ['source_id' => $source_id]);
        foreach ($result as $item) {
            $item = json_decode(json_encode($item), true);
            array_push($line_item, $item);
        }
        return $line_item;
    }

    public function setSourceStatus($source_id, $status, $id, $message) {
        return DB::insert('INSERT INTO source_status (source_id, status, error_code_id, error_description, date) VALUES (?, ?, ?, ?, ?)',
           [$source_id, $status, $id, $message, date('Y-m-d h:i:s')]);
    }

    public function searchForLineItem($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['line_item'] === $id) {
                return $key;
            }
        }
        return null;
    }

    public function setBySourceFullSplit($arrData) {
        extract($arrData);

        $metrics_daily = DB::select("SELECT * FROM metric_by_source_and_full_split_daily WHERE `publisher_id` = :id AND `date` = :date AND `source_id` = :source_id AND `device` = :device AND `product_type_id` = :product_type_id AND `geo` = :geo AND `slot` = :slot AND `ad_size` = :ad_size",
        ['id' => $publisher_id, 'date' => $date, 'source_id' => $source_id, 'device' => $device, 'product_type_id' => $product_type_id, 'geo' => $geo, 'slot' => $slot, 'ad_size' => $ad_size]);

        if(count($metrics_daily)){
            $metrics_daily = $metrics_daily[0];

            $impressions = $impressions + $metrics_daily->impressions;
            $net_revenue = $net_revenue + $metrics_daily->net_revenue;
            $gross_revenue = $gross_revenue + $metrics_daily->gross_revenue;

            DB::update('UPDATE metric_by_source_and_full_split_daily SET `impressions` = ?, `net_revenue` = ?, `gross_revenue` = ? WHERE `publisher_id` = ? AND `date` = ? AND `source_id` = ? AND `device` = ? AND `product_type_id` = ? AND `geo` = ? AND `slot` = ? AND `ad_size` = ?',
            [$impressions, $net_revenue, $gross_revenue, $publisher_id, $date, $source_id, $device, $product_type_id, $geo, $slot, $ad_size]);

        } else {

            DB::insert('INSERT INTO metric_by_source_and_full_split_daily (date, source_id, publisher_id, impressions, device, product_type_id, geo, slot, ad_size, net_revenue, gross_revenue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$date, $source_id, $publisher_id, $impressions, $device, $product_type_id, $geo, $slot, $ad_size, $net_revenue, $gross_revenue]);
        }
    }

    public function setByFullSplit($arrData) {
        extract($arrData);

        $metrics_daily = DB::select("SELECT * FROM metric_by_full_split_daily WHERE `publisher_id` = :id AND `date` = :date AND `device` = :device AND `product_type_id` = :product_type_id AND `geo` = :geo AND `slot` = :slot AND `ad_size` = :ad_size",
        ['id' => $publisher_id, 'date' => $date, 'device' => $device, 'product_type_id' => $product_type_id, 'geo' => $geo, 'slot' => $slot, 'ad_size' => $ad_size]);

        if(count($metrics_daily)){
            $metrics_daily = $metrics_daily[0];

            $impressions = $impressions + $metrics_daily->impressions;
            if (!is_null($page_views)) {
                $page_views = $page_views + $metrics_daily->page_views;
            } else {
                $page_views = $metrics_daily->page_views;
            }
            $net_revenue = $net_revenue + $metrics_daily->net_revenue;
            $gross_revenue = $gross_revenue + $metrics_daily->gross_revenue;

            DB::update('UPDATE metric_by_full_split_daily SET `page_views` = ?, `impressions` = ?, `net_revenue` = ?, `gross_revenue` = ? WHERE `publisher_id` = ? AND `date` = ? AND `device` = ? AND `product_type_id` = ? AND `geo` = ? AND `slot` = ? AND `ad_size` = ?',
            [$page_views, $impressions, $net_revenue, $gross_revenue, $publisher_id, $date, $device, $product_type_id, $geo, $slot, $ad_size]);

        } else {

            DB::insert('INSERT INTO metric_by_full_split_daily (date, publisher_id, page_views, impressions, device, product_type_id, geo, slot, ad_size, net_revenue, gross_revenue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$date, $publisher_id, $page_views, $impressions, $device, $product_type_id, $geo, $slot, $ad_size, $net_revenue, $gross_revenue]);
        }
    }

    public function refreshMetrics($params) {
        $numDeleted = DB::delete("DELETE FROM metric_by_full_split_daily WHERE date(`date`) BETWEEN :start AND :end", ['start' => $params['start'], 'end' => $params['end']]);
        DB::statement("insert into metric_by_full_split_daily (`date`, publisher_id, page_views, impressions, net_revenue, gross_revenue, geo, slot, ad_size, device, product_type_id) select `date`, publisher_id, sum(page_views) as page_views, sum(impressions) as impressions, sum(net_revenue) as net_revenue, sum(gross_revenue) as gross_revenue,
        geo, slot, ad_size, device, product_type_id from metric_by_source_and_full_split_daily where date(`date`) BETWEEN :start AND :end group by date, publisher_id, geo, slot, ad_size, device, product_type_id;", ['start' => $params['start'], 'end' => $params['end']]);
    }

    public function refreshAllMetrics($params) {
        DB::statement("DELETE FROM metric_by_full_split_daily");
        DB::statement("insert into metric_by_full_split_daily (`date`, publisher_id, page_views, impressions, net_revenue, gross_revenue, geo, slot, ad_size, device, product_type_id) select `date`, publisher_id, sum(page_views) as page_views, sum(impressions) as impressions, sum(net_revenue) as net_revenue, sum(gross_revenue) as gross_revenue,
        geo, slot, ad_size, device, product_type_id from metric_by_source_and_full_split_daily group by date, publisher_id, geo, slot, ad_size, device, product_type_id;");
    }

    public function clearSourceMetrics($source_id, $params) {
        $numDeleted = DB::delete("DELETE FROM metric_by_source_and_full_split_daily WHERE `source_id` = :source_id and date(`date`) BETWEEN :start AND :end", ['source_id' => $source_id, 'start' => $params['start'], 'end' => $params['end']]);
    }


    public function statistics($params = [], $publisher) {

        $data = [];

        $d = [
            'date1' => $params['start'],
            'date2' => $params['end']
        ];

        $q = '
            SELECT sum(net_revenue) as revenue,  sum(impressions) as impressions, 1000* sum(net_revenue) / sum(impressions) as ecpm
            FROM metric_by_full_split_daily
            WHERE
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
        ';

        $stats = DB::select($q, $d);

        $data['impressions'] = $stats[0]->impressions;
        $data['revenue'] = $stats[0]->revenue;
        $data['ecpm'] = $stats[0]->ecpm;
        return $data;
    }

    public function dashboard($params) {

        $res['error'] = false;

        $params['start'] = (new \DateTime($params['start']))->format('Y-m-d H:i:s');
        $params['end'] = (new \DateTime($params['end']))->format('Y-m-d H:i:s');

        //setup queries
        $d = [
            'date1' => $params['start'],
            'date2' => $params['end']
        ];
        $publisher = '';

        if ($params['publisher']) {
            $publisher = 'AND publisher_id=' . $params['publisher'];
        } else if($params['user']) {
            $user = \App\User::find($params['user']);

            if(!$user->is_superuser) {
                $publishers = $this->getPublishersUsers($params['user']);
                $arrPublishers = [];

                for($i = 0; $i < count($publishers); $i++) {
                    array_push($arrPublishers, $publishers[$i]->publisher_id);
                }
                $strPublishers = implode (",", $arrPublishers);

                $publisher = 'AND publisher_id IN ('.$strPublishers.')';
            }
        }

        // statistics week
        $res['statics_week'] = $this->statistics($params, $publisher);

        // statists last week
        $res['statics_last_week'] = $this->statistics(array_merge($params, [
            'start' => (new \DateTime($params['start']))->modify('-1 week')->format('Y-m-d H:i:s'),
            'end' => (new \DateTime($params['end']))->modify('-1 week')->format('Y-m-d H:i:s')
        ]), $publisher);

        // impressions
        $data = [];
        $result = DB::select('
            SELECT sum(`impressions`) as impressions, DATE_FORMAT(`date`,\'%b %d\') as `date`
            FROM `metric_by_full_split_daily`
            WHERE
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `date`
        ', $d);

        foreach ($result as $impression) {
            $data['date'] = $impression->date;
            $data['impressions'] = $impression->impressions;
            $impressions[] = $data;
        }
        $res['impressions'] = $impressions;


        // page views
        $data = [];
        $page_views = [];
        $result = DB::select('
            SELECT sum(`page_views`) as page_views, DATE_FORMAT(`date`,\'%b %d\') as `date`, `device`
            FROM `metric_by_full_split_daily`
            WHERE
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `device`,`date`
        ', $d);

        foreach ($result as $item) {
            $data['date'] = $item->date;
            $data['page_views'] = $item->page_views;
            $data['device'] = $item->device;
            $page_views[] = $data;
        }

        $data = [];
        $result = DB::select('
            SELECT sum(`page_views`) as page_views
            FROM `metric_by_full_split_daily`
            WHERE
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
        ', $d);

        foreach ($result as $item) {
            if($item->page_views) {
                $data['page_views'] = $item->page_views;
                $data['device'] = 'all';
                $page_views[] = $data;
            }
        }

        $res['page_views'] = $page_views;


        // effective Revenue
        // Hack for contextual
        $data = [];
        $result = DB::select('
            SELECT device, name as product_name, 1000* sum(net_revenue) / sum(impressions) as ecpm
            FROM metric_by_full_split_daily as a INNER JOIN product_type as b using (product_type_id)
            WHERE a.`product_type_id` != 1 and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `device`, `product_name`
        ', $d);

        foreach ($result as $effective_revenue) {
            $product_name = explode(' ', $effective_revenue->product_name);
            $data['device'] = $effective_revenue->device;
            $data['ecpm'] = $effective_revenue->ecpm;
            $data['product_name'] = strtolower($product_name[0]);
            $effective_revenues[] = $data;
        }

        $data = [];
        $result = DB::select('
            SELECT device, name as product_name, 1000* sum(net_revenue) / sum(page_views) as ecpm
            FROM metric_by_full_split_daily as a INNER JOIN product_type as b using (product_type_id)
            WHERE a.`product_type_id` = 1 and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `device`, `product_name`
        ', $d);

        foreach ($result as $effective_revenue) {
            $product_name = explode(' ', $effective_revenue->product_name);
            $data['device'] = $effective_revenue->device;
            $data['ecpm'] = $effective_revenue->ecpm;
            $data['product_name'] = strtolower($product_name[0]);
            $effective_revenues[] = $data;
        }

        $result = DB::select('
            SELECT name as product_name, 1000* sum(net_revenue) / sum(impressions) as ecpm
            FROM metric_by_full_split_daily as a INNER JOIN product_type as b using (product_type_id)
            WHERE a.`product_type_id` != 1 and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `product_name`
        ', $d);

        foreach ($result as $effective_revenue) {
            $product_name = explode(' ', $effective_revenue->product_name);
            $data['device'] = 'all';
            $data['ecpm'] = $effective_revenue->ecpm;
            $data['product_name'] = strtolower($product_name[0]);
            $effective_revenues[] = $data;
        }

        $result = DB::select('
            SELECT name as product_name, 1000* sum(net_revenue) / sum(page_views) as ecpm
            FROM metric_by_full_split_daily as a INNER JOIN product_type as b using (product_type_id)
            WHERE a.`product_type_id` = 1 and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `product_name`
        ', $d);

        foreach ($result as $effective_revenue) {
            $product_name = explode(' ', $effective_revenue->product_name);
            $data['device'] = 'all';
            $data['ecpm'] = $effective_revenue->ecpm;
            $data['product_name'] = strtolower($product_name[0]);
            $effective_revenues[] = $data;
        }

        $res['effective_revenue'] = $effective_revenues;


        // site stats
        $data = [];
        $result = DB::select('
            SELECT geo, sum(impressions) as impressions, 1000* sum(net_revenue) / sum(impressions) as ecpm
            FROM `metric_by_full_split_daily`
            WHERE impressions is not null and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `geo`
        ', $d);

        foreach ($result as $site_stats) {
            $data['impressions'] = $site_stats->impressions;
            $data['ecpm'] = $site_stats->ecpm;
            $data['geo'] = $site_stats->geo;
            $sites_stats[] = $data;
        }
        $res['site_stats'] = $sites_stats;


        // earned revenue
        $data = [];
        $result = DB::select('
            SELECT name as product_name, sum(`net_revenue`) as revenue, a.product_type_id, device
            FROM `metric_by_full_split_daily` as a INNER JOIN product_type as b using (product_type_id)
            WHERE
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY `device`, `product_name`
        ', $d);

        foreach ($result as $earned_revenue) {
            $data['revenue'] = $earned_revenue->revenue;
            $data['product'] = $earned_revenue->product_name;
            $data['device'] = $earned_revenue->device;
            $earneds_revenue[] = $data;
        }

        $res['earned_revenue'] = $earneds_revenue;


        // performance
        $arrTypes['display'] = [];
        $arrTypes['contextual'] = [];
        $arrTypes['native'] = [];
        $arrTypes['video'] = [];

        // Display
        $data = array();
        $result = DB::select('
            SELECT  m.`product_type_id`,m.`ad_size`,SUBSTRING_INDEX(m.`slot`, "-", -3) as `slotp`,
                1000* sum(net_revenue) / sum(impressions) as ecpm, SUBSTRING_INDEX(p.`name`, " ", 1) as `name`
            FROM `metric_by_full_split_daily` as m INNER JOIN `product_type` as p ON m.`product_type_id` = p.`product_type_id`
            WHERE
                `date` BETWEEN :date1 AND :date2 AND m.`product_type_id` = 5
                '.$publisher.'
            GROUP BY `slotp`, m.`product_type_id`,m.`ad_size` ORDER BY m.`slot`, m.`ad_size` ASC
        ', $d);

        foreach ($result as $performance) {
            $data['ad_size'] = $performance->ad_size;
            $data['ecpm'] = $performance->ecpm;
            $data['name'] = $performance->name;

            if(!array_key_exists(strtolower($performance->slotp), $arrTypes[strtolower($performance->name)])) {
                $arrTypes[strtolower($performance->name)][strtolower($performance->slotp)] = array();
            }
            array_push($arrTypes[strtolower($performance->name)][strtolower($performance->slotp)], $data);
        }

        // Native
        $data = array();
        $result = DB::select('
            SELECT  m.`product_type_id`,SUBSTRING_INDEX(m.`slot`, "-", -3) as `slotp`,
                1000* sum(net_revenue) / sum(impressions) as ecpm, SUBSTRING_INDEX(p.`name`, " ", 1) as `name`
            FROM `metric_by_full_split_daily` as m INNER JOIN `product_type` as p ON m.`product_type_id` = p.`product_type_id`
            WHERE
                `date` BETWEEN :date1 AND :date2 AND m.`product_type_id` = 3
                '.$publisher.'
            GROUP BY `slotp`, m.`product_type_id`
        ', $d);

        foreach ($result as $performance) {
            $data['slot'] = $performance->slotp;
            $data['ecpm'] = $performance->ecpm;
            $data['name'] = $performance->name;

            array_push($arrTypes[strtolower($performance->name)], $data);
        }

        // Contextual
        $data = array();
        $result = DB::select('
            SELECT  m.`product_type_id`,SUBSTRING_INDEX(m.`slot`, "-", -3) as `slotp`,
                1000* sum(net_revenue) / sum(page_views) as ecpm, SUBSTRING_INDEX(p.`name`, " ", 1) as `name`
            FROM `metric_by_full_split_daily` as m INNER JOIN `product_type` as p ON m.`product_type_id` = p.`product_type_id`
            WHERE
                `date` BETWEEN :date1 AND :date2 AND m.`product_type_id` = 1
                '.$publisher.'
            GROUP BY `slotp`, m.`product_type_id`
        ', $d);

        foreach ($result as $performance) {
            $data['slot'] = $performance->slotp;
            $data['ecpm'] = $performance->ecpm;
            $data['name'] = $performance->name;

            array_push($arrTypes[strtolower($performance->name)], $data);
        }

        $data = array();
        $result = DB::select('
            SELECT 1000* sum(net_revenue) / sum(impressions) as ecpm, p.`name`
            FROM `metric_by_full_split_daily` as m INNER JOIN `product_type` as p ON m.`product_type_id` = p.`product_type_id`
            WHERE m.`product_type_id` != 1 and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY m.`product_type_id`
        ', $d);

        foreach ($result as $performance) {
            $data['ecpm'] = $performance->ecpm;
            $data['name'] = $performance->name;

            if(!array_key_exists('all', $arrTypes)){
                $arrTypes['all'] = array();
            }
            array_push($arrTypes['all'], $data);

            // video
            if($performance->name == 'Video'){
               $data['ecpm'] = $performance->ecpm;
               $data['name'] = $performance->name;

               array_push($arrTypes[strtolower($performance->name)], $data);
            }
        }

        $data = array();
        $result = DB::select('
            SELECT 1000* sum(net_revenue) / sum(page_views) as ecpm, p.`name`
            FROM `metric_by_full_split_daily` as m INNER JOIN `product_type` as p ON m.`product_type_id` = p.`product_type_id`
            WHERE m.`product_type_id` = 1 and
                `date` BETWEEN :date1 AND :date2
                '.$publisher.'
            GROUP BY m.`product_type_id`
        ', $d);

        foreach ($result as $performance) {
            $data['ecpm'] = $performance->ecpm;
            $data['name'] = $performance->name;

            if(!array_key_exists('all', $arrTypes)){
                $arrTypes['all'] = array();
            }
            array_push($arrTypes['all'], $data);

        }


        $performances = $arrTypes;

        $res['performance'] = $performances;

        return $res;
    }

    public function username() {
        return $this->_username;
    }

    public function password() {
        return $this->_password;
    }

    public function client() {
        return $this->_client;
    }

    public function matchStandardDisplayLineItem($s) {
        $x = preg_match($this->_displayLineItemRe, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 9 || $count == 7) {
                if ($count == 9) {
                    $retval["ad_size"] = $matches[8];
                }
                $retval["publisher_id"] = intval($matches[1]);
                $device = $matches[2];
                if ($device === "app") {
                    $device = "mob";
                }
                $retval["device"] = $device;
                $retval["geo"] = $matches[6];
                $retval["slot"] = implode("-", array_slice($matches, 2, 4));
            }
        }
        return $retval;
    }

    public function matchStandardNativeLineItem($s) {
        $x = preg_match($this->_nativeLineItemRe, strtolower($s), $matches);
        if ($x) {
            $count = count($matches);
            if ($count == 4 || $count == 6) {
                if ($count == 6) {
                    $retval["ad_size"] = $matches[5];
                }
                $retval["publisher_id"] = intval($matches[1]);
                $retval["slot"] = $matches[2];
                $retval["geo"] = $matches[3];
            }
        }
        return $retval;
    }

    // $keys is an array of keys of interest
    // $array is an associative array
    public function array_keys_missing($keys, $array){
        if ($keys) {
            if (!$array) {
                return $keys;
            } else {
                foreach($keys as $key){
                    if(!array_key_exists($key, $array)) {
                        $missing[] = $key;
                    }
                }
            }
        }
        return $missing;
    }

    public function chooseFromThree($x1, $x2, $default) {
        if ($x1) {
            return $x1;
        } else if ($x2) {
            return $x2;
        } else {
            return $default;
        }
    }

    public function chooseFromTwo($x1, $default) {
        return $x1 ? $x1 : $default;
    }

    public function getShortName($n) {
        // @TODO - This doesn't work for the dupont sites that end with .com.homes,...
        $x = preg_match($this->_shortNameRe, strtolower($n), $matches);
        if ($x) {
            $retval = $matches[1];
        }
        return $retval;
    }

    public function writeRowsToMetricBySourceAndFullSplitDaily($consolidated) {
        // @TODO - Make this into one insert
        foreach ($consolidated as $row) {
            $keys = implode(', ', array_keys($row));
            $values = "'" .implode("','", array_values($row)) . "'";
            $DIRECTIVE = 'INSERT INTO metric_by_source_and_full_split_daily('.$keys.') VALUES ('.$values.')';
            DB::insert($DIRECTIVE, $row);
        }
    }

    public function removeBadPublisherIds($consolidated, &$badPublisherIds) {
        $filtered = [];
        $checked = [];
        foreach ($consolidated as $row) {
            $publisher_id = $row['publisher_id'];
            $idType = null;
            if (array_key_exists($publisher_id, $checked)) {
                $idType = $checked[$publisher_id];
            } else {
                $publisher = $this->getPublisher($publisher_id);
                if (!count($publisher)) {
                    $idType = 1;
                    $badPublisherIds[$publisher_id] = 1;
                } else {
                    $idType = 2;
                }
                $checked[$publisher_id] = $idType;
            }
            if ($idType == 2) {
                $filtered[] = $row;
            }
        }
        return $filtered;
    }

    public function logUnrecognizedImportedPublishers($badPublisherIds, $source_id) {
        foreach ($badPublisherIds as $pid => $flag) {
            $message = $pid;
            $this->setSourceStatus($source_id, 'Server Error', 6, $message);
        }
    }

    public function getPublisherIdFromPublisherName($n) {
        if ($n) {
            $nLC = strtolower($n);
            $ps = $this->publishers();
            foreach ($ps as $p) {
                $site_code = strtolower($p->site_code);
                $site_name = strtolower($p->site_name);
                // Short name is already lowercased
                if (($nLC === $site_name) || ($nLC === $site_code) ||($nLC === $p->shortName)) {
                    $retval = $p->publisher_id;
                    break;
                }
            }
            if (!retval) {
                // Second priority match
                foreach ($ps as $p) {
                    $alt_name = strtolower($p->alt_name);
                    // Short name is already lowercased
                    if ($nLC === $alt_name) {
                        $retval = $p->publisher_id;
                        break;
                    }
                }
            }
            if (!$retval) {
                if ($nLC === "dupontregistry") {
                    $retval = 5;
                } else if ($nLC === "activetimes") {
                    $retval = 59;                
                }
            }
        }
        return $retval;
    }

    public function consolidateDataRows($input) {
        $retval = null;
        if (!empty($input)) {
            $lookup = [];
            foreach ($input as $j => $row) {
                $key = $this->createStringKey([$row['date'], $row['publisher_id'], $row['geo'], $row['ad_size'], $row['slot'], $row['device']]);
                if (array_key_exists($key, $lookup)) {
                    $existing = $lookup[$key];
                    $existing['impressions'] = $existing['impressions'] + $row['impressions'];
                    $existing['gross_revenue'] = $existing['gross_revenue'] + $row['gross_revenue'];
                    $existing['net_revenue'] = $existing['net_revenue'] + $row['net_revenue'];
                    // Would like to use references, but PHP semantics for references are not intuitive and thus bug-prone
                    $lookup[$key] = $existing;
                } else {
                    $lookup[$key] = $input[$j];
                }
            }
            $retval = array_values($lookup);
        }
        return $retval;
    }


    private function _getRevenues(){
        $result = DB::select("SELECT * FROM `revenue_share`");
        $retval = [];
        foreach ($result as $revenue) {
            $retval[$revenue->revenue_share_id] = $revenue;
        }
        return $retval;
    }

    private function _getPublishers() {
        $ps = DB::select("SELECT publisher_id, name, site_name, site_code FROM `publisher` WHERE active = 1");
        foreach ($ps as $p) {
            $p->shortName = $this->getShortName($p->site_code);
            $p->siteNameLC = strtolower($p->site_name);
            $p->altName = $this->_getAltName($p->siteNameLC);
            $retval[$p->publisher_id] = $p;
        }
        return $retval;
    }

    private function _getAltName($name) {
        if ($name) {
            $altName = strtolower(preg_replace('/\s/', '', $name));
        }
        return $altName;
    }


    public function getUsersPublishers($publisher_id) {
        return DB::select("SELECT u.id, u.email FROM user_publisher AS up INNER JOIN users AS u ON up.user_id = u.id WHERE `publisher_id` = :publisher_id", ['publisher_id' => $publisher_id]);
    }

    public function getPublishersUsers($user_id) {
        return DB::select("SELECT publisher_id FROM user_publisher WHERE `user_id` = :user_id", ['user_id' => $user_id]);
    }

    public function editPublisher($publisher_id, $name, $code, $code_ga, $client_fraction, $client_fraction_old, $users_removed, $user_id) {
        $res['error'] = false;

        try {
            DB::update('UPDATE publisher SET `name` = ?, `site_name` = ?, `site_code` = ?, `site_ga` = ? WHERE `publisher_id` = ?', [$code, $name, $code, $code_ga, $publisher_id]);
            DB::update('UPDATE revenue_share SET `client_fraction` = ?, `date_modified` = ? WHERE `publisher_id` = ?', [$client_fraction, date('Y-m-d h:i:s'), $publisher_id]);

            if($client_fraction != $client_fraction_old) {
                DB::insert('INSERT INTO revenue_share_historical (publisher_id, client_fraction_new, client_fraction_old, date_added, user_id) VALUES (?, ?, ?, ?, ?);', [$publisher_id, $client_fraction, $client_fraction_old, date('Y-m-d h:i:s'), $user_id]);
            }

            for($i = 0; $i < count($users_removed); $i++) {
                DB::delete('DELETE FROM user_publisher WHERE `user_id` = ? and `publisher_id` = ?', [$users_removed[$i]['id'], $publisher_id]);
            }
        } catch (\Exception $e) {
            $res['error'] = true;
            return $res;
        }
        return $res;
    }

    public function savePublisher($publisher_id, $name, $code, $code_ga, $client_fraction, $user_id) {
        $res['error'] = false;

        try {
            DB::insert('INSERT INTO publisher (publisher_id, name, site_name, site_code, site_ga) VALUES (?, ?, ?, ?, ?)',[$publisher_id, $code, $name, $code, $code_ga]);
            DB::insert('INSERT INTO revenue_share (publisher_id, client_fraction, date_added) VALUES (?, ?, ?)',[$publisher_id, $client_fraction, date('Y-m-d h:i:s')]);
            DB::insert('INSERT INTO revenue_share_historical (publisher_id, client_fraction_new, date_added, user_id) VALUES (?, ?, ?, ?)',[$publisher_id, $client_fraction, date('Y-m-d h:i:s'), $user_id]);
        } catch (\Exception $e) {
            $res['error'] = true;
            $res['message'] = 'Publisher ID already used.';
            return $res;
        }
        $res['message'] = 'Publisher added successfully!';
        return $res;
    }

    public function logErrors() {
        return DB::select("SELECT s.status, e.name AS type, DATE_FORMAT(s.date, '%m/%d/%Y %h:%i %p') as date, s.error_description AS message
                           FROM `source_status` AS s INNER JOIN `error_code` as e
                           ON s.error_code_id = e.error_code_id ORDER BY source_status_id DESC limit 100");
    }

    public function metrics($params) {
        $res['error'] = false;

        $params['start'] = (new \DateTime($params['start']))->format('Y-m-d H:i:s');
        $params['end'] = (new \DateTime($params['end']))->format('Y-m-d H:i:s');

        //setup queries
        $d = [
            'date1' => $params['start'],
            'date2' => $params['end']
        ];

        $data = [];
        $result = DB::select('
            SELECT sum(gross_revenue) as gross_revenue, sum(net_revenue) as revenue, sum(gross_revenue - net_revenue) as desk_revenue, m.publisher_id, p.name
            FROM `metric_by_full_split_daily` AS m INNER JOIN publisher AS p ON m.publisher_id = p.publisher_id
            WHERE
                `date` BETWEEN :date1 AND :date2
            GROUP BY `publisher_id`
        ', $d);

        $result_sum = DB::select('
            SELECT sum(gross_revenue) as gross_revenue, sum(net_revenue) as revenue, sum(gross_revenue - net_revenue) as desk_revenue
            FROM `metric_by_full_split_daily` AS m
            WHERE
                `date` BETWEEN :date1 AND :date2
        ', $d);

        $gross_revenue_total = $revenue_total = $desk_revenue_total = 0;
        if (count($result_sum) == 1) {
            $gross_revenue_total = $result_sum[0]->gross_revenue;
            $revenue_total = $result_sum[0]->revenue;
            $desk_revenue_total = $result_sum[0]->desk_revenue;
        }
        foreach ($result as $metrics) {
            $data['gross_revenue'] = $metrics->gross_revenue;
            $data['revenue'] = $metrics->revenue;
            $data['desk_revenue'] = $metrics->desk_revenue;
            $data['publisher_id'] = $metrics->publisher_id;
            $data['name'] = $metrics->name;
            $metrics_financial[] = $data;
        }
        $res['publishers'] = $metrics_financial;
        $res['total']['gross_revenue'] = $gross_revenue_total;
        $res['total']['revenue'] = $revenue_total;
        $res['total']['desk_revenue'] = $desk_revenue_total;

        return $res;
    }

    private function in_array_r($item , $array){
        return preg_match('/"'.$item.'"/i' , json_encode($array));
    }

    public function getAllUsers() {
        $result = DB::select("SELECT id, u.first_name, u.last_name, email, is_superuser, up.publisher_id, p.name
                                FROM users AS u LEFT JOIN user_publisher AS up ON u.id = up.user_id
                                LEFT JOIN publisher AS p ON up.publisher_id = p.publisher_id
                                WHERE u.`active` = 1 ORDER BY u.last_name, u.first_name;");

        $users_publishers = [];
        foreach ($result as $users) {

            if(!$this->in_array_r($users->email , $users_publishers)){
                $data['user_id'] = $users->id;
                $data['first_name'] = $users->first_name;
                $data['last_name'] = $users->last_name;
                $data['name'] = Source::getFullName($users->first_name, $users->last_name);
                $data['email'] = $users->email;
                $data['is_superuser'] = $users->is_superuser;
                $data['publishers'] = [];

                if(!$data['is_superuser'] && $users->publisher_id) {
                    $publisher = [];
                    $publisher['publisher_id'] = $users->publisher_id;
                    $publisher['name'] = $users->name;
                    array_push($data['publishers'], $publisher);
                }

                $users_publishers[] = $data;
            } else {
                for($i = 0; $i < count($users_publishers); $i++) {
                    if($users_publishers[$i]['email'] == $users->email && !$users_publishers[$i]['is_superuser'] && $users->publisher_id) {
                        $publisher = [];
                        $publisher['publisher_id'] = $users->publisher_id;
                        $publisher['name'] = $users->name;
                        array_push($users_publishers[$i]['publishers'], $publisher);
                    }
                }
            }
        }
        return $users_publishers;
    }

    public function disablePublisher($publisher_id) {
        $res['error'] = false;

        try {
            DB::update('UPDATE publisher SET `active` = 0 WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM user_publisher WHERE `publisher_id` = ?', [$publisher_id]);

        } catch (\Exception $e) {
            $res['error'] = true;
            return $res;
        }
        return $res;
    }

    public function deletePublisher($publisher_id) {
        $res['error'] = false;

        try {
            DB::delete('DELETE FROM line_item WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM metric_by_full_split_daily WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM metric_by_source_and_full_split_daily WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM revenue_historical WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM revenue_share WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM user_publisher WHERE `publisher_id` = ?', [$publisher_id]);
            DB::delete('DELETE FROM publisher WHERE `publisher_id` = ?', [$publisher_id]);

        } catch (\Exception $e) {
            $res['error'] = true;
            return $res;
        }
        return $res;
    }

    public function getAllPublishers() {
        $publishers = DB::select('
            SELECT p.publisher_id, p.name, r.client_fraction, p.site_name, p.site_code, p.site_ga
                FROM `publisher` as p INNER JOIN `revenue_share` as r
                ON p.publisher_id = r.publisher_id
                WHERE p.active = 1
                ORDER BY p.site_code ASC;
        ');

        $pubs = [];
        foreach ($publishers as $publisher) {
            $pub['publisher_id'] = (int)$publisher->publisher_id;
            $pub['name'] = $publisher->name;
            $pub['site_name'] = $publisher->site_name;
            $pub['site_code'] = $publisher->site_code;
            $pub['site_ga'] = $publisher->site_ga;
            $pub['client_fraction'] = $publisher->client_fraction;
            $pubs[] = $pub;
        }
        return $pubs;
    }

    public function updateUserPublishers($user_id, $firstName, $lastName, $email, $is_superuser, $publishers_removed, $publishers_add) {
        $res['error'] = false;

        $fullName = Source::getFullName($firstName, $lastName);

        try {
            DB::update('UPDATE users SET `name` = ?, `first_name` = ?, `last_name` = ?, `email` = ?, `is_superuser` = ? WHERE `id` = ?', [$fullName, $firstName, $lastName, $email, $is_superuser, $user_id]);

            if($is_superuser) {
                DB::delete('DELETE FROM user_publisher WHERE `user_id` = ?', [$user_id]);
            } else {
                for($i = 0; $i < count($publishers_removed); $i++) {
                    DB::delete('DELETE FROM user_publisher WHERE `user_id` = ? and `publisher_id` = ?', [$user_id, $publishers_removed[$i]['publisher_id']]);
                }
            }

            for($i = 0; $i < count($publishers_add); $i++) {
                DB::insert('INSERT INTO user_publisher (user_id, publisher_id) VALUES (?, ?)',[$user_id, $publishers_add[$i]]);
            }

        } catch (\Exception $e) {
            $res['error'] = true;
            return $res;
        }
        return $res;
    }

    public function createUser($firstName, $lastName, $email, $password, $remember_token, $is_superuser, $publishers_add) {
        $res['error'] = false;

        $fullName = Source::getFullName($firstName, $lastName);

        try {
            $superuser = $is_superuser == 'admin' ? 1 : 0;

            DB::insert('INSERT INTO users (name, first_name, last_name, email, password, remember_token, is_superuser, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
               [$fullName, $firstName, $lastName, $email, $password, $remember_token, $superuser, date('Y-m-d h:i:s')]);

            $last_id = DB::getPdo()->lastInsertId();
            for($i = 0; $i < count($publishers_add); $i++) {
                DB::insert('INSERT INTO user_publisher (user_id, publisher_id) VALUES (?, ?)',
                  [$last_id, $publishers_add[$i]]);
            }
        } catch (\Exception $e) {
            $res['error'] = true;
            $res['message'] = 'Error adding user.';
            return $res;
        }
        $res['message'] = 'User added successfully!';
        return $res;
    }


    // @TODO - Move some of these functions to a different base case because not all classes need this

    protected function extractInternationalFromAllAndUSArrays($allData, $usData) {
        $usLookup = $this->convertToLookup($usData);
        $intlData = $this->calculateInternational($allData, $usLookup);
        return $intlData;
    }

    public static function createStringKey($values) {
        return implode(',', $values);
    }

    private function convertToLookup($myArray) {
        $result = [];
        foreach($myArray as $i => $item) {
            $key = Source::createStringKeyForUSAndAllMerge($item);
            $result[$key] = &$myArray[$i];
        }
        return $result;
    }

    private function calculateInternational($allData, $usLookup) {
        $result = [];
        $checkedKeys = [];
        $impressionsKey = "impressions";
        $grossRevenueKey = "gross_revenue";
        $netRevenueKey = "net_revenue";
        foreach($allData as $i => $item) {
            // @TODO: This explicitly uses the name for line item and ad size - not consistent with above
            $key = Source::createStringKeyForUSAndAllMerge($item);
            if (array_key_exists($key, $usLookup)) {
                if (array_key_exists($key, $checkedKeys)){
                    Log::error("Source::calculateInterational: not unique key: ".$key);
                } else {
                    $usItem = $usLookup[$key];
                    $usImpressions = $usItem[$impressionsKey];              
                    $diffImpressions = Source::extractNumber($item[$impressionsKey]) - $usImpressions;
                    // If the number of impressions is the same, then it should be only US data
                    if ($diffImpressions > 0) {
                        $usGrossRevenue = $usItem[$grossRevenueKey];
                        $diffGrossRevenue = Source::extractNumber($item[$grossRevenueKey]) - $usGrossRevenue;

                        $usNetRevenue = $usItem[$netRevenueKey];
                        $diffNetRevenue = Source::extractNumber($item[$netRevenueKey]) - $usNetRevenue;
                        // $item is an array, so this clones it
                        $intlItem = $item;
                        $intlItem[$impressionsKey] = $diffImpressions;
                        $intlItem[$grossRevenueKey] = $diffGrossRevenue;
                        $intlItem[$netRevenueKey] = $diffNetRevenue;
                        $result[] = $intlItem;
                    }
                }
            } else {
                $result[] = &$allData[$i];
            }
            $checkedKeys[$key] = 1;
        }
        return $result;

    }

    public static function sumOverKey($arr, $key, $removeDollarSign=false) {
        $total = 0;
        if ($arr) {
            foreach ($arr as $a) {
                $value = $a[$key];
                if ($removeDollarSign) {
                    $value = ltrim($value, '$');
                }
                $value = Source::extractNumber($value);
                $total += $value;
            }
        }
        return $total;

    }

    public static function getFullName($firstName, $lastName) {
        if ($firstName && $lastName) {
            $fullName = $firstName." ".$lastName;
        } else if ($firstName) {
            $fullName = $firstName;
        } else {
            $fullName = $lastName;
        }
        return $fullName;
    }

    protected function getMinAndMaxDates($date_field, $dateFormat, ...$arrs) {
        $curMin = null;
        $curMax = null;
        $dates = [];
        foreach ($arrs as $arr) {
            $this->extractDates($arr, $date_field, $dates);
        }
        $dtFormat = $dateFormat.' H:i:s';
        foreach ($dates as $date => $flag) {
            $dt = DateTime::createFromFormat($dtFormat, $date." 00:00:00");
            if ($dt) {
                if (!$curMin || $dt < $curMin) {
                    $curMin = $dt;
                }
                if (!$curMax || $dt > $curMax) {
                    $curMax = $dt;
                }
                $this->_dateLookup[$date] = $dt->format('Y-m-d');
            }
        }
        if ($curMin) {
            $curMin = $curMin->format('Y-m-d');
        }
        if ($curMax) {
            $curMax = $curMax->format('Y-m-d');
        }
        return ['min' => $curMin, 'max' => $curMax];
    }

    private function extractDates($arr, $date_field, &$outputDates) {
        foreach ($arr as $key => $value) {
            $date = $value[$date_field];
            $outputDates[$date] = 1;
        }
    }

    public function dateLookup() {
        return $this->_dateLookup;
    }

    public function product_type_id() {
        return $this->_product_type_id;
    }

    public function source_id() {
        return $this->_source_id;
    }

    public function source() {
        if (!$this->_source && $this->_source_id) {
            $this->_source = Source::getSourceFromDb($this->_source_id);
        }
        return $this->_source;
    }

    public static function extractNumber($s) {
        if ($s || is_numeric($s)) {
            $parsed = floatval(str_replace(",", "", $s));
        }       
        return $parsed;
    }

    public static function cleanUpAdSize($s) {
        $s = trim($s);
        $s = str_replace(' ', '', $s);
        return $s;
    }

    public static function createStringKeyForUSAndAllMerge($row) {
        return Source::createStringKey([$row['date'], $row['publisher_id'], $row['ad_size'], $row['slot'], $row['device']]);
    }

    protected function checkSpreadsheetBadHeaders($ws, $headerRow) {
        $badHeaders = null;
        if ($this->_spreadsheetCols) {
            foreach ($this->_spreadsheetCols as $colName => $colNum) {
                $colVal = $ws->getCellByColumnAndRow($colNum, $headerRow)->getFormattedValue();
                if (!($colVal === $colName)) {
                    $badHeaders[] = $colVal;
                }
            }
        }
        return $badHeaders;
    }

}
