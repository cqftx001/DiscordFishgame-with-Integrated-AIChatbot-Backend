<?php

use Illuminate\Support\Facades\Route;

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
Route::post('chat/general', [\App\Http\Controllers\ChatGPTController::class, 'chat']);
Route::post('chat/command', [\App\Http\Controllers\ChatGPTController::class, 'command']);
Route::post('chat/draw', [\App\Http\Controllers\ChatGPTController::class, 'draw']);
Route::group(['middleware' => ['myauth']], function () {
    //检查用户是否存在
    Route::get('user/is-exist', [\App\Http\Controllers\UserController::class, 'existOrNot']);
    //创建用户基本信息
    Route::post('user/basic', [\App\Http\Controllers\UserController::class, 'store']);
    //用户必须存在
    Route::group(['middleware' => ['myverify']], function () {
        //获取用户基本信息
        Route::get('user/basic', [\App\Http\Controllers\UserController::class, 'basic']);
        //获取用户账户信息
        Route::get('user/finance', [\App\Http\Controllers\UserController::class, 'finance']);
        //获取用户等级信息
        Route::get('user/level', [\App\Http\Controllers\UserController::class, 'level']);
        //获取用户库存信息
        Route::get('user/inventory', [\App\Http\Controllers\UserController::class, 'inventory']);
        //获取用户成就信息
        Route::get('user/achievement', [\App\Http\Controllers\UserController::class, 'achievement']);
        //钓鱼
        Route::post('fish/catch', [\App\Http\Controllers\FishController::class, 'catch']);
        //卖鱼
        Route::post('fish/sell', [\App\Http\Controllers\FishController::class, 'sell']);

        // 商城
        Route::get('shop/list', [\App\Http\Controllers\ShopController::class, 'listItems']);
        Route::post('shop/purchase', [\App\Http\Controllers\ShopController::class, 'purchaseItems']);
    });
});



