<?php

use Illuminate\Support\Facades\Route;
use Modules\Akap\app\Http\Controllers\AkapMonthlyController;
use Modules\Akap\app\Http\Controllers\AkapDailyController;

use Modules\Akap\app\Http\Controllers\AkapMonthlyTwinController;

use Modules\Akap\app\Http\Controllers\AkapMonthlyReportController;
use Modules\Akap\app\Http\Controllers\AkapDailyReportController;

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
        Route::get('bulanan-old', [AkapMonthlyController::class, 'index']);
        Route::get('harian-old', [AkapDailyController::class, 'index']);
        Route::get('harian', [AkapDailyReportController::class, 'index']);
        Route::get('harian/tickets', [AkapDailyReportController::class, 'getTicket']);
        Route::get('harian/buy', [AkapDailyReportController::class, 'getBuy']);

        Route::get('bulanan-twin', [AkapMonthlyTwinController::class, 'index']);

        Route::get('bulanan', [AkapMonthlyReportController::class, 'index']);
        Route::get('bulanan/tickets', [AkapMonthlyReportController::class, 'getTicket']);
        Route::get('bulanan/buy', [AkapMonthlyReportController::class, 'getBuy']);
        Route::get('bulanan/tickets-prev', [AkapMonthlyReportController::class, 'getTicketPrevMonth']);

    });
});
