<?php

use Illuminate\Support\Facades\Route;
use Modules\Akap\app\Http\Controllers\AkapMonthlyController;
use Modules\Akap\app\Http\Controllers\AkapDailyController;

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

Route::middleware(['auth'])->group(function () {
    Route::prefix('akap')->group(function () {
        Route::get('bulanan', [AkapMonthlyController::class, 'index']);
        Route::get('harian', [AkapDailyController::class, 'index']);
    });
});
