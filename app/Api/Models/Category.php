<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = 'categories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'deleted'
    ];

}
