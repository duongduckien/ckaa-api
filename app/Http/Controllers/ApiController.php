<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ApiController extends Controller {

    use \App\Http\Requests\ApiResponse;

    protected $defaultIncludes = [];

    public function __construct()
    {

    }

}
