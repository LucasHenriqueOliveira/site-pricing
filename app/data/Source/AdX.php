<?php

namespace App\Data\Source;

class AdX extends \App\Data\Source {
    private $_config;
    private $_googleClient;
    private $_googleService;
    private $_accountId;

    public function __construct($params = []) {
        $this->_client = new \GuzzleHttp\Client;
        $this->_config = [
             "type"=> "service_account"
        ];

        if (array_key_exists('adx_private_key', $params)) {
            $this->_config["private_key"] = $params['adx_private_key'];

            if (array_key_exists('adx_client_email', $params)) {
                $this->_config["client_email"] = $params['adx_client_email'];
                if (array_key_exists('adx_accountId', $params)) {
                    $this->_accountId = $params['adx_accountId'];

                    $this->_googleClient = new Google_Client();
                    $this->_googleClient->setAuthConfig($this->_config);
                    $this->_googleClient->addScope('https://www.googleapis.com/auth/adexchange.seller.readonly');
                    // @TODO - is this a good place for this?
                    $this->_googleService = new Google_Service_AdExchangeSeller($this->_googleClient);
                }
            }

        }
        $params['source_id'] = 15;
        $params['product_type_id'] = 5; // Display
        parent::__construct($params);
    }

    public function googleClient() {
        return $_googleClient;
    }

    public function googleService() {
        return $_googleService;
    }

    public function report($params = []) {
        // $url = 'http://console.unrulymedia.com/publisher/reporting/custom_user_reports?site_id='.$this->user().'&metrics=row_VideoImpression&metrics=row_CTRate&metrics=row_PublisherEarnings&requestNoPadding=noPad&operatingSystem=&product=&playerType=&country=ON_TARGET_SUBCAMPAIGN&period=a+quick+date+range&start='.$this->formatDate($params['start']).'&end='.$this->formatDate($params['start']).'&dateInterval=day&breakdown1=NETWORK_EXT_PUB&breakdown2=&orderby=%24statsQuery.orderByColumnForFormatter&csv=&layout=&showGraph=&graphType=';
        $url = 'http://console.unrulymedia.com/publisher/reporting/custom_user_reports?user_id=27542&metrics=row_VideoImpression&metrics=row_PublisherEarnings&requestNoPadding=noPad&operatingSystem=UNKNOWN_MOBILE&countryFilter=US&product=&playerType=&country=ON_TARGET_SUBCAMPAIGN&period=a+quick+date+range&start=24%2F03%2F2017&end=30%2F03%2F2017&dateInterval=day&breakdown1=NETWORK_EXT_PUB&breakdown2=&orderby=%24statsQuery.orderByColumnForFormatter&layout=&showGraph=&graphType=';
        $res = $this->client()->request('GET', $url, [
            'cookies' => $this->jar(),
            'headers' => [
                'User-Agent' => $this->agent()
            ],
        ]);

        print_r($res->getBody()->getContents());
        exit;

        return $processData($res);
    }

}