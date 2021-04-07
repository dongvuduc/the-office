<?php

use Illuminate\Support\Facades\Route;

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

Route::get('login', [\App\Http\Controllers\OAuthController::class, 'login']);
Route::get('callback', [\App\Http\Controllers\OAuthController::class, 'callback']);
Route::get('user', [\App\Http\Controllers\OAuthController::class, 'user']);
Route::post('import-data', [\App\Http\Controllers\OAuthController::class, 'import'])->name('import');