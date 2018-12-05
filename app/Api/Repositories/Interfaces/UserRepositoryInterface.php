<?php

namespace Api\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function createUser($user);

    public function removeUser($id);

    public function getUserById($id);

    public function updateUserWhereId($id, $data);
}
