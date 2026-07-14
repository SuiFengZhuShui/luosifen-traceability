<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', 'Api\AuthController@logout');
    Route::get('dispatch/records', 'Api\DispatchController@index');
    Route::get('receiving-units', 'Api\DispatchController@receivingUnits');
    Route::post('dispatch', 'Api\DispatchController@store');
    Route::post('dispatch/ocr', 'Api\DispatchController@ocr');
});