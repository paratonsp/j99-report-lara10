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

Route::prefix('pengaturan')->group(function () {
    Route::get('akap', [PengaturanController::class, 'akap']);
    Route::post('akap', [PengaturanController::class, 'akapCreate']);
    Route::patch('akap', [PengaturanController::class, 'akapUpdate']);
    Route::get('pariwisata', [PengaturanController::class, 'pariwisata']);
});
