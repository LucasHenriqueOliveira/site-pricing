<?php

namespace App\Data\Source;
use PHPHtmlParser\Dom;
use Log;
use App\Data\Source;

class Unruly extends \App\Data\Scrape {
    public $_user;
    public $_user_id;


    public function __construct($params = []) {
        $params['source_id'] = 10;
        $params['product_type_id'] = 3; // Native
        parent::__construct($params);
    }

    public function login() {
        $res = $this->client()->request('POST', 'https://console.unrulymedia.com/j_acegi_security_check', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
            'form_params' => [
                'j_username' => $this->username(),
                'j_password' => $this->password(),
                'rememberMe' => true,
                'submit' => 'Login'
            ],
            'allow_redirects' => [
                'referer'         => true,
                'track_redirects' => true
            ]
        ]);

        // failed to login
        if (!$res->getHeader('X-Guzzle-Redirect-History')) {
            return false;
        }

        $user = preg_match_all('/sites\/(.*)/i', $res->getHeader('X-Guzzle-Redirect-History')[1], $matches);
        $this->_user = $matches[1][0];

        $res = $this->client()->request('GET', 'http://console.unrulymedia.com/ssp/sites/' . $this->user(), [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
        ]);

        $dom = new Dom;
        $dom->load($res->getBody()->getContents());
        $a = $dom->getElementById('header-custom-reporting')->getAttribute('href');
        $str = parse_url($a)['query'];
        parse_str($str, $arr);
        $this->_user_id = $arr['user_id'];

        return true;
    }

    private function formatDate($date) {
        return (new \DateTime($date))->format('m/d/Y');
    }

    private function formatDateTime($date) {
        return $date. ' 00:00:00';
    }

    public function report($params = []) {
        if($params['device'] == 'mob') {
            $filter = 'UNKNOWN_MOBILE';
        } else {
            $filter = 'UNKNOWN_NON_MOBILE';
        }

        $res = $this->client()->request('GET', 'http://console.unrulymedia.com/publisher/reporting/custom_user_reports?user_id='. $this->userId() .'&metrics=row_VideoImpression&metrics=row_PublisherEarnings&requestNoPadding=noPad&operatingSystem='.$filter.'&product=&playerType=&country=ON_TARGET_SUBCAMPAIGN&period=a+quick+date+range&start='.$this->formatDate($params['start']).'&end='.$this->formatDate($params['start']).'&dateInterval=day&breakdown1=NETWORK_EXT_PUB&breakdown2=country_code&orderby=%24statsQuery.orderByColumnForFormatter&csv=&layout=&showGraph=&graphType=', [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
        ]);

        $data = $this->csvToAssoc($res->getBody()->getContents());

        return $data;
    }

    public function import($params) {

        ini_set('max_execution_time', 180);

        $source_id = $this->source_id();
        try {
            $res = $this->login();
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 2, $e->getMessage());
        }

        try {
            $report = $this->report([
                'start' => $params['start'],
                'device' => $params['device']
            ]);
        } catch (\Exception $e) {
            return $this->setSourceStatus($source_id, 'Server Error', 3, $e->getMessage());
        }

        $source = $this->source();
        $product_type_id = $this->product_type_id();

        if(!$report) {
            return $this->setSourceStatus($source_id, 'Server Error', 5, 'No data returned');
        }

        //metrics
        foreach ($report as $item) {
            if(strtolower($item['Site']) != 'total') {

                $publisher = $this->getPublisherFromName(strtolower($item['Site']));
                if($publisher) {

                    if($item['Country'] == 'United States (US)') {
                        $country = 'us';
                    } else {
                        $country = 'in';
                    }

                    $arrData['date'] = $this->formatDateTime($params['start']);
                    $arrData['publisher_id'] = $publisher->publisher_id;
                    $arrData['device'] = $params['device'];
                    $arrData['geo'] = $country;
                    $arrData['product_type_id'] = $product_type_id;
                    $arrData['source_id'] = $source_id;
                    $arrData['impressions'] = $item[$source->impressions_field];
                    $arrData['ad_size'] = '';
                    $arrData['slot'] = '';
                    $arrData['page_views'] = '';

                    try {
                        // search client_fraction in revenue_share table
                        $revenue_share = $this->getRevenueShare($arrData['publisher_id']);
                        $arrData['gross_revenue'] = $item[$source->gross_revenue_field];
                        $arrData['net_revenue'] = $item[$source->gross_revenue_field] * (float)$revenue_share->client_fraction;
                    } catch (\Exception $e) {
                        $this->setSourceStatus($source_id, 'Server Error', 6, $e->getMessage());
                    }

                    try {
                        $this->setBySourceFullSplit($arrData);
                    } catch (\Exception $e) {
                        $this->setSourceStatus($source_id, 'Server Error', 9, $e->getMessage());
                    }
                } else {
                    $this->setSourceStatus($source_id, 'Server Error', 6, strtolower($item['Site']));
                }
            }
        }

        $this->refreshMetrics($params);
        return true;
    }

    public function user() {
        return $this->_user;
    }

    public function userId() {
        return $this->_user_id;
    }
}