<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exception\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Dashboard extends BaseController{

    public function dashboard(Request $request) {
        $dashboard = new \App\Data\Source();

        $res = $dashboard->dashboard([
            'start' => $request->input('start'),
            'end' => $request->input('end'),
            'publisher' => $request->input('publisher'),
            'user' => $request->input('user')
        ]);

        echo json_encode($res);
        exit;
    }

    public function metrics(Request $request) {
        $dashboard = new \App\Data\Source();

        $res = $dashboard->metrics([
            'start' => $request->input('start'),
            'end' => $request->input('end')
        ]);

        echo json_encode($res);
        exit;
    }
}