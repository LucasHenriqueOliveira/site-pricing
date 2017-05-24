<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Exception\HttpResponseException;

class TestController extends Controller {

    public function __construct(){
        if (!$_ENV['APP_ENV']) {
            foreach($_SERVER as $key => $value) {
                $_ENV[$key] = $value;
            }
        }
    }

    public function email(Request $request) {
        $email = new \App\Data\Email();
        $res = $email->login();
        exit;
    }

    public function rubiconJob(Request $request) {
        dispatch(new \App\Jobs\Rubicon([
            'start' => '2017-02-01',
            'end' => '2017-02-05'
        ]));
        return response()->json(true);
    }


    public function unruly(Request $request) {
        $unruly = new \App\Data\Source\Unruly([
                'username' => $_ENV['UNRULY_USERNAME'],
                'password' => $_ENV['UNRULY_PASSWORD']
            ]);
        $res = $unruly->login();
        $res = $unruly->report([
                'start' => '2017-02-01',
                'end' => '2017-03-01'
            ]);

        print_r($res);
        exit;
    }

    public function teads(Request $request) {
        $teads = new \App\Data\Source\Teads([]);
        $res = $teads->import(['source_id' => 23]);
        die('ok');
        exit;
    }


    public function undertone(Request $request) {
        $undertone = new \App\Data\Source\Undertone([
                'username' => $_ENV['UNDERTONE_USERNAME'],
                'password' => $_ENV['UNDERTONE_PASSWORD']
            ]);
        $res = $undertone->login();
        $res = $undertone->orders([
                'start' => '2017-03-01',
                'end' => '2017-03-01'
            ]);

        print_r($res);
        exit;

    }

    public function criteo(Request $request) {

        $criteo = new \App\Data\Source\Criteo([
                'token' => $_ENV['CRITEO_TOKEN']
            ]);
        $res = $criteo->report([
                'start' => '2017-02-15',
                'end' => '2017-02-17'
            ]);

        print_r($res);
        exit;
    }

    public function adyoulike(Request $request) {

        $adyoulike = new \App\Data\Source\AdYouLike([
                'username' => $_ENV['ADYOULIKE_USERNAME'],
                'password' => $_ENV['ADYOULIKE_PASSWORD']
            ]);
        $res = $adyoulike->login();
        // $res = $adyoulike->harvestSingleDay([
        //         'date' => '2017-02-12',
        //         'geo' => 'us'
        //     ]);
        // $res = $adyoulike->ingestSingleDay([
        //         'date' => '2017-02-12'
        //     ]);
        $res = $adyoulike->import([
                'start' => '2017-02-08',
                'end' => '2017-02-12'
            ]);
        print_r($res);
        exit;

    }

    public function triplelift(Request $request) {
        $triplelift = new \App\Data\Source\TripleLift([
                'username' => $_ENV['TRIPLELIFT_USERNAME'],
                'password' => $_ENV['TRIPLELIFT_PASSWORD']
            ]);
        $res = $triplelift->login();
        $res = $triplelift->report([
                'start' => '2017-02-15',
                'end' => '2017-02-17',
                'publisher' => '575'
            ]);

        print_r($res);
    }


    public function connatix(Request $request) {

        $connatix = new \App\Data\Source\Connatix([
                'username' => $_ENV['CONNATIX_USERNAME'],
                'password' => $_ENV['CONNATIX_PASSWORD']
            ]);
        $res = $connatix->login();
        $res = $connatix->download([
                'start' => '2017-02-15',
                'end' => '2017-02-21'
            ]);

        print_r($res);

    }

    public function rubicon(Request $request) {
        $rubicon = new \App\Data\Source\Rubicon([
                'username' => $_ENV['RUBICON_USERNAME'],
                'password' => $_ENV['RUBICON_PASSWORD']
            ]);

        $res = $rubicon->login();
        $res = $rubicon->download([
                'start' => '2017-02-15',
                'end' => '2017-02-15'
            ]);
        print_r($res);
        exit;

    }

    public function taboola(Request $request) {

        $taboola = new \App\Data\Source\Taboola([
                'username' => $_ENV['TABOOLA_USERNAME'],
                'password' => $_ENV['TABOOLA_PASSWORD']
            ]);

        $res = $taboola->login();
        // var_dump($res);

        // $res = $taboola->harvestSingleDay([
        //     'date' => '2017-03-22',
        //     'geo' => 'ALL',
        //     'device' => 'other'
        // ]);
        // print_r($res);
        // $res = $taboola->ingestSingleDay([
        //     'date' => '2017-03-24',
        //     'device' => 'other'
        // ]);
        // print_r($res);
        $res = $taboola->import([
            'start' => '2017-03-22',
            'end'   => '2017-03-24'
        ]);
        print_r($res);
    }

    public function padsquad(Request $request) {
        $padSquad = new \App\Data\Source\PadSquad([
                'username' => $_ENV['PADSQUAD_USERNAME'],
                'password' => $_ENV['PADSQUAD_PASSWORD']
            ]);

        $res = $padSquad->import([
            'start' => '2017-03-22',
            'end'   => '2017-03-24',
            'source_id' => 20
        ]);
        print_r($res);
    }

    public function sovrn(Request $request) {
        $sovrn = new \App\Data\Source\Sovrn([
                'username' => $_ENV['SOVRN_USERNAME'],
                'password' => $_ENV['SOVRN_PASSWORD']
            ]);
        $res = $sovrn->login();
        // $res = $sovrn->harvestSovrn([
        //     'start' => '03/12/2017',
        //     'end' => '03/13/2017'
        // ]);
        // print_r($res);
        $result = $sovrn->import([
            'start' => '03/14/2017',
            'end' => '03/15/2017',
            'source_id' => 2
        ]);
        print_r($result);


        // $res = $sovrn->earnings([
        //         // 'start' => '2017-02-15',
        //         // 'end' => '2017-02-21',
        //         'start' => '2017-03-11',
        //         'end' => '2017-03-12',
        //         'site' => $sovrn->websites()[0]->site
        //     ]);
        // print_r($res);

        // $res = $sovrn->overview([
        //         'start' => '2017-02-15',
        //         'end' => '2017-02-21',
        //         'site' => $sovrn->websites()[0]->site
        //     ]);
        // print_r($res);
    }

    public function mediabong(Request $request) {

        $mediabong = new \App\Data\Source\MediaBong([
                'username' => $_ENV['MEDIABONG_USERNAME'],
                'password' => $_ENV['MEDIABONG_PASSWORD']
            ]);
        $res = $mediabong->login();
            //$res = $mediabong->group();
            $res = $mediabong->ecpm([
                'start' => '2017-02-15',
                'end' => '2017-02-21'
            ]);
        return response()->json($res);
    }

    public function technorati(Request $request) {

        $technorati = new \App\Data\Source\Technorati([
            'username' => $_ENV['TECHNORATI_USERNAME'],
            'password' => $_ENV['TECHNORATI_PASSWORD']
        ]);

        $res = $technorati->login();
        $res = $technorati->report([
            'start' => '2017-03-01',
            'end' => '2017-03-02'
        ]);
        return response()->json($res);
    }

    public function appnexus(Request $request) {

        $appNexus = new \App\Data\Source\AppNexus([
            'username' => $_ENV['APPNEXUS_USERNAME'],
            'password' => $_ENV['APPNEXUS_PASSWORD']
        ]);

        $res = $appNexus->login();
        $res = $appNexus->report([
            'start' => '2017-03-01',
            'end' => '2017-03-02'
        ]);
        return response()->json($res);
    }

    public function sublimeskins(Request $request) {

        $sublimeskins = new \App\Data\Source\SublimeSkins([
            'apiKey' => $_ENV['SUBLIMESKINZ_KEY'],
            'apiSecret' => $_ENV['SUBLIMESKINZ_SECRET']
        ]);

        $res = $sublimeskins->report([
            'start' => '2017-03-01',
            'end' => '2017-03-02'
        ]);
        return response()->json($res);
    }

    public function realvu(Request $request) {

        $realvu = new \App\Data\Source\Realvu([
            'username' => $_ENV['REALVU_USERNAME'],
            'password' => $_ENV['REALVU_PASSWORD']
        ]);

        $res = $realvu->login();

        $res = $realvu->report([
            'start' => '2017-02-01',
            'end' => '2017-04-04'
        ]);
        return response()->json($res);
    }


    public function aol(Request $request) {
        $aol = new \App\Data\Source\Aol([]);
        $res = $aol->import(['source_id' => 14]);
        die('ok');
        exit;
    }

}