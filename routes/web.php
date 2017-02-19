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
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization");

Route::get('/', function () {
    echo 'api root';
});


Route::group(['prefix' => 'app', 'middleware' => 'cors'], function () {

    Route::post('/auth/register', 'AuthController@register');

    Route::post('/auth/login', 'AuthController@authenticate');

});


Route::group(['prefix' => 'app', 'middleware' => ['cors', 'jwt.auth']], function () {

    // get feeds and first set of feed items
    #Route::get('/feedsanditems', 'AppController@everything');

    // get subscriptions for a user
    Route::get('/subscriptions', 'AppController@getSubscriptions');

    // get feed items
    Route::get('/feedsandcategories', 'AppController@getFeedItems');

    // add new feed
    Route::post('/feeds/new', 'AppController@newFeed');
});


Route::get('/process', ['uses' => 'AutoController@process']);
