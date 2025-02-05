<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\app\Http\Controllers\CmsController;

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
    Route::prefix('cms')->group(function () {
        Route::get('address', [CmsController::class, 'listAddress']);
        Route::get('address/add', [CmsController::class, 'addAddress']);
    });
});