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

/*
 * Authentication
 */
Route::post('authorize', 'AuthController@authenticate');

/*
 * Users
 */
Route::post('users', 'UserController@create');
Route::post('users/{id}', 'UserController@remove');
Route::get('users', 'UserController@get');

/*
 * Products
 */
Route::get('products/{catId}', 'ProductController@getWhereCat');
Route::get('product/{id}', 'ProductController@get');
Route::post('product', 'ProductController@create');
Route::put('product/{id}', 'ProductController@edit');
Route::delete('product/{id}', 'ProductController@remove');

/*
 * Categories
 */
Route::get('category/{id}', 'CategoryController@get');
Route::post('category', 'CategoryController@create');
Route::put('category/{id}', 'CategoryController@edit');
Route::delete('category/{id}', 'CategoryController@remove');
Route::get('categories', 'CategoryController@getAll');
