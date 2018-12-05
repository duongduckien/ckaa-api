<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{

    public function createUser(UserRequest $request) {

        $user = [
            "username" => $request->get('username'),
            "email" => $request->get('email'),
            "password" => $request->get('password')
        ];



    }

}
