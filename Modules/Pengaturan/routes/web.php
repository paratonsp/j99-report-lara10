<?php

use Illuminate\Support\Facades\Route;
use Modules\Pengaturan\app\Http\Controllers\PengaturanController;

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
    Route::prefix('pengaturan')->group(function () {
        Route::get('akap-trip', [PengaturanController::class, 'akapTrip']);
        Route::post('akap-trip', [PengaturanController::class, 'akapTripCreate']);
        Route::patch('akap-trip', [PengaturanController::class, 'akapTripUpdate']);
        Route::get('akap-target', [PengaturanController::class, 'akapTarget']);
        Route::post('akap-target', [PengaturanController::class, 'akapTargetCreate']);
        Route::patch('akap-target', [PengaturanController::class, 'akapTargetUpdate']);
        Route::delete('akap-target', [PengaturanController::class, 'akapTargetDelete']);
        Route::get('pariwisata', [PengaturanController::class, 'pariwisata']);
    });
});
