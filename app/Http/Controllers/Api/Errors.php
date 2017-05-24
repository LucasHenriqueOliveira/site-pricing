<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exception\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Errors extends BaseController{

    public function logErrors(Request $request) {
        $errors = new \App\Data\Source();

        $res = $errors->logErrors();

        echo json_encode($res);
        exit;
    }
}