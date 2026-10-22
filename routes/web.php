<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsermanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('dashboard');
});

Route::get('login', [LoginController::class, 'index']);
Route::post('login', [LoginController::class, 'authenticate'])->name('login');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('reports/{report}')
    ->where(['report' => 'insurance|flexi'])
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\AddonReportController::class, 'index'])->name('reports.addons');
        Route::get('/export', [\App\Http\Controllers\AddonReportController::class, 'export'])->name('reports.addons.export');
    });
