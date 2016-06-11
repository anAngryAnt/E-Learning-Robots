<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('home', [
    'as' => 'home',
    'uses' => 'HomeController@index'
]);

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);

Route::group(['prefix' => 'hack'], function () {

    Route::post('/scratch',
        [
            'as' => 'scratch',
            'uses' => 'Hack\HackerHandler@scratchInfo'
        ]);

    Route::get('/test', 'Hack\HackerHandler@test');

    Route::get('/refresh', [
        'as' => 'refresh',
        'uses' => 'Hack\HackerHandler@refreshTime'
    ]);
});

