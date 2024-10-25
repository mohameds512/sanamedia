<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ContentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::group(['prefix'=> 'content'], function (){
        Route::post('create', [ContentController::class, 'create']);
        Route::post('index', [ContentController::class, 'index']);
    });

});

Route::get('material/{folder}/{item}/{no_cache}', [ContentController::class, 'material'])->name('material');
