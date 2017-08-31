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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
  //  return $request->user();
//});


Route::group(['prefix' => 'api/v1'], function() {// Here we group all routes which has same prefix
    Route::resource('meeting', 'MeetingController', [ //Here we are creating meeting resource routes except edit and create
        'except' => ['edit', 'create'] // Except this routes
    ]);

    Route::resource('meeting/registration', 'RegistrationController', [ //Here we are creating registration resource controller routes except edit and create
        'only' => ['store', 'destroy'] // only this routes
    ]);

    Route::post('user', [ //These are our routes for authentication and we create it in default way
      'uses' => 'AuthController@store'
    ]);

    Route::post('user/signin', [ //These are our routes for authentication and we create it in default way
      'uses' => 'AuthController@signin'
    ]);
});
