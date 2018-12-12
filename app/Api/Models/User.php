<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use Notifiable;

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

    protected $hidden = [
        'password',
        'remember_token'
    ];


    public function rewriteCredentials($credentials)
    {
        return [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'deleted' => 0,
            'block' => 0
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
