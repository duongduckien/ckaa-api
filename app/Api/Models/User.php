<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'password',
        'username',
        'email',
        'avatar',
        'path',
        'block',
        'phone',
        'role',
        'deleted'
    ];

}
