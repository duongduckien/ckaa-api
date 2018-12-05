<?php

namespace App\Http\Controllers;

use Api\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function create(UserRequest $request)
    {

        $user = [
            "username" => $request->get('username'),
            "email" => $request->get('email'),
            "password" => \Hash::make($request->get('password'))
        ];

        $result = $this->userRepository->createUser($user);

        if ($result) {
            return $this->respondCreated([
                'id' => $result->id,
                'username' => $result->username,
                'email' => $result->email
            ]);
        }

        return $this->respondInternalError('An error ocurred while creating user');

    }

}
