<?php

use Illuminate\Support\Facades\Route;
use Modules\Masterdata\app\Http\Controllers\MasterdataBusController;
use Modules\Masterdata\app\Http\Controllers\MasterdataClassController;
use Modules\Masterdata\app\Http\Controllers\MasterdataFacilitiesController;

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

Route::middleware(['auth','has-permission'])->group(function () {
    Route::prefix('masterdata')->group(function () {
        Route::get('bus', [MasterdataBusController::class, 'listMasterBus']);
        Route::get('bus/add', [MasterdataBusController::class, 'addMasterBus']);
        Route::post('bus/add', [MasterdataBusController::class, 'addMasterBusStore']);
        Route::get('class', [MasterdataClassController::class, 'listMasterClass']);
        Route::get('class/add', [MasterdataClassController::class, 'addMasterClass']);
        Route::post('class/add', [MasterdataClassController::class, 'addMasterClassStore']);
        Route::get('facilities', [MasterdataFacilitiesController::class, 'listMasterFacilities']);
        Route::get('facilities/add', [MasterdataFacilitiesController::class, 'addMasterFacilities']);
        Route::post('facilities/add', [MasterdataFacilitiesController::class, 'addMasterFacilitiesStore']);
    });
});