<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// CORS
#header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization");

Route::get('/', function () {
    if(Auth::check()) {
        echo "logged in";
    }else{
        echo "logged out";
    }
});


Route::group(['prefix' => 'app', 'middleware' => 'cors'], function () {


    Route::get('/auth/register', 'AuthController@register');

    Route::get('/auth/login', 'AuthController@authenticate');

    Route::get('/auth/logout', function () {
        return view('welcome');
    });

    Route::get('/auth/authenticated', [function () {
        return response()->json([
            'authStatus' => true
        ]);
    }]);

    Route::get('/auth/getauthed', 'AuthController@getAuthenticatedUser');

    Route::get('/feedsandcategories', 'AppController@everything');

    Route::post('/feeds/new', 'AppController@newFeed');


});
