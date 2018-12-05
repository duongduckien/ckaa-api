<?php

namespace Api\Repositories;

use App\Api\Models\User;
use Api\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function createUser($user)
    {
        $result = $this->user->create($user);
        return $result;
    }

}
