<?php

namespace App\Http\Requests;

use Symfony\Component\HttpFoundation\Response as IlluminateResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    public function response(array $errors)
    {
        return Response::json([
            'error' => 'Validation failed',
            'fields' => $errors,
        ],
            IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
