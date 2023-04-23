<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

php artisan cache:clear
php artisan view:clear
php artisan route:cache
php artisan config:cache
php artisan optimize

*/

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */
Route::group(['middleware' => 'api'], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/user', [AuthController::class, 'userProfile']);

    Route::post('/clients', [ClientController::class, 'createClient']);
    Route::delete('/clients/{id}', [ClientController::class, 'removeClient']);
    Route::get('/kpi-clients', [ClientController::class, 'kpiClients']);
    Route::get('/clients/{id?}', [ClientController::class, 'getClient']);
});
