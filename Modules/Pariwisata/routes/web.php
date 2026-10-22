<?php

use Illuminate\Support\Facades\Route;
use Modules\Pariwisata\app\Http\Controllers\PariwisataController;

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
    Route::get('pariwisata/bulanan', [PariwisataController::class, 'index'])->name('pariwisata.bulanan');
    Route::get('pariwisata/harian', [PariwisataController::class, 'daily'])->name('pariwisata.harian');
});
