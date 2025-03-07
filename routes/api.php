<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JokeUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserCardController;

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

//Route::resource('joke_user', 'JokeUserController');

//Route::get('list', [JokeUserController::class, 'index']);
Route::post('register', [JokeUserController::class, 'store']);
Route::post('login', [HomeController::class, 'login']);
Route::get('home', [HomeController::class, 'home']);
Route::post('register-card', [UserCardController::class, 'store']);
Route::post('list-cards', [UserCardController::class, 'index']);
Route::post('charge-card', [UserCardController::class, 'charge']);

