<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exception\HttpResponseException;

class Publisher extends BaseController{

    public function publishers(Request $request) {
        $publisher = new \App\Data\Source();

        $res = $publisher->getAllPublishers();

        echo json_encode($res);
        exit;
    }

    public function getUsers(Request $request) {
        $publisher = new \App\Data\Source();

        $res = $publisher->getUsersPublishers($request->input('publisher_id'));

        echo json_encode($res);
        exit;
    }

    public function editPublisher(Request $request) {
        $publisher = new \App\Data\Source();

        $res = $publisher->editPublisher($request->input('publisher_id'),
            $request->input('site_name'),
            $request->input('site_code'),
            $request->input('site_ga'),
            $request->input('client_fraction'),
            $request->input('client_fraction_old'),
            $request->input('users_removed'),
            $request->input('user_id'));

        echo json_encode($res);
        exit;
    }

    public function savePublisher(Request $request, $id) {

        $publisher = new \App\Data\Source();

        $res = $publisher->savePublisher($request->input('publisher_id'),
            $request->input('site_name'),
            $request->input('site_code'),
            $request->input('site_ga'),
            $request->input('client_fraction'),
            $request->input('user_id'));

        echo json_encode($res);
        exit;
    }

    public function deletePublisher(Request $request, $id) {
        $publisher = new \App\Data\Source();

        $publisher->deletePublisher($id);

        echo json_encode($res);
        exit;
    }

    public function disablePublisher(Request $request, $id) {
        $publisher = new \App\Data\Source();

        $publisher->disablePublisher($id);

        echo json_encode($res);
        exit;
    }
}