<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exception\HttpResponseException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;

class Users extends BaseController {

    public function requireSuper() {
        if (!JWTAuth::parseToken()->toUser()->is_superuser) {
            http_response_code(404);
            exit;
        }
        return true;
    }

    public function users(Request $request) {
        $this->requireSuper();

        $users = new \App\Data\Source();
        $res = $users->getAllUsers();

        echo json_encode($res);
        exit;
    }

    public function saveNewUser(Request $request) {
        $this->requireSuper();

        $user_publishers = new \App\Data\Source();
        $res = $user_publishers->createUser($request->input('first_name'), $request->input('last_name'), $request->input('email'), Hash::make($request->input('password')), str_random(10), $request->input('type'), $request->input('publishers_add'));

        echo json_encode($res);
        exit;
    }

    public function updateUser(Request $request, $id) {
        $this->requireSuper();

        $user_publishers = new \App\Data\Source();
        $res = $user_publishers->updateUserPublishers($id, $request->input('first_name'), $request->input('last_name'), $request->input('email'), $request->input('is_superuser'), $request->input('publishers_removed'), $request->input('publishers_add'));

        echo json_encode($res);
        exit;
    }

    public function getUser(Request $request, $id) {
        $this->requireSuper();

        $user = \App\User::find($id);
        echo json_encode($user);
        exit;
    }

    public function deleteUser(Request $request, $id) {
        $this->requireSuper();

        $res['error'] = false;

        try {
            $user = \App\User::find($id);
            $user->active = 0;
            $user->save();
        } catch (\Exception $e) {
            $res['error'] = true;
        }

        echo json_encode($res);
        exit;
    }
}