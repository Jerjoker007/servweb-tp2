<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AuthController;



Route::middleware('throttle:5,1')->group(function () {
    Route::post('/signup', 'App\Http\Controllers\AuthController@register');
    Route::post('/signin', 'App\Http\Controllers\AuthController@login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', 'App\Http\Controllers\AuthController@getUser');
        Route::post('/refresh', 'App\Http\Controllers\AuthController@refreshToken');
        Route::post('/signout', 'App\Http\Controllers\AuthController@logout');
    });
});