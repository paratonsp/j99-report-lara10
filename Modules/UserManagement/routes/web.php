<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\app\Http\Controllers\AccountController;
use Modules\UserManagement\app\Http\Controllers\RoleController;
use Modules\UserManagement\app\Http\Controllers\MenuController;

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
    Route::prefix('usermanagement')->group(function () {
        Route::get('account', [AccountController::class, 'listAccount']);
        Route::get('account/add', [AccountController::class, 'addAccount']);
        Route::post('account/add', [AccountController::class, 'addAccountStore']);
        Route::get('role', [RoleController::class, 'listRole']);
        Route::get('role/add', [RoleController::class, 'addRole']);
        Route::post('role/add', [RoleController::class, 'addRoleStore']);
        Route::get('role/permission/{role_uuid}', [RoleController::class, 'permissionForm']);
        Route::post('role/permission/{role_uuid}', [RoleController::class, 'permissionStore']);
        Route::get('menu', [MenuController::class, 'listMenu']);
        Route::get('menu/add', [MenuController::class, 'addMenu']);
        Route::post('menu/add', [MenuController::class, 'addMenuStore']);
    });
});
