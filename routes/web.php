<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OutController;

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

Route::get('/', function () {
    return view('welcome');
});


Route::post('sociallogin/{provider}', 'Auth\AuthController@SocialSignup');

// you need the post method to work with apple
Route::post('auth/{provider}/callback', 'Auth\AuthAppController@AppleCode');

Route::get('auth/{provider}/redirect', [OutController::class, 'redirect'])->where('provider', '.*');
Route::get('auth/{provider}/callback', [OutController::class, 'callback'])->where('provider', '.*');