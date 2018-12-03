<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('version', function () {
    return response()->json([
        'version' => "1.0"
    ]);
});

Route::get('users', function () {
    return response()->json([
        "1" => [
            "email" => "admin@gmail.com",
            "name" => "David A",
            "role" => "Administrator"
        ],
        "2" => [
            "email" => "user1@gmail.com",
            "name" => "Anna",
            "role" => "User"
        ],
        "3" => [
            "email" => "user2@gmail.com",
            "name" => "Manis",
            "role" => "User"
        ]
    ]);
});

Route::post('users', function () {
    return response()->json([
        "status" => "Success",
        "message" => "User created successfully!"
    ]);
});

Route::delete('users', function () {
    return response()->json([
        "status" => "Success",
        "message" => "User deleted successfully!"
    ]);
});