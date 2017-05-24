<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exception\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Jobs extends BaseController{

    public function jobs(Request $request) {
        if (!JWTAuth::parseToken()->toUser()->is_superuser) {
            http_response_code(404);
            exit;
        }

        $queued = app('db')->select('select * from jobs');
        $failed = app('db')->select('select * from failed_jobs');

        echo json_encode([
            'queued' => $queued,
            'failed' => $failed
        ]);

        exit;
    }
}