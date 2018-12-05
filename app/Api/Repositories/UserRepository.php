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

    public function removeUser($id)
    {
        $result = $this->user->whereId($id)->update([
           'deleted' => 1
        ]);
        return $result;
    }

    public function getUserById($id)
    {
        $user = $this->user->where('id', $id)->first();
        return $user;
    }

    public function updateUserWhereId($id, $data)
    {
        $this->user->where('id', $id)->update([
            'deleted' => 1
        ]);
    }

}
