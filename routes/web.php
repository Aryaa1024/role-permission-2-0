<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('login');
});

Route::group(['prefix' => 'account', 'as' => 'admin.', 'middleware' => ['auth']], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
});
Route::group(['prefix' => 'account', 'middleware' => ['auth', 'checkPermission']], function () {

    Route::group(['prefix' => 'roles', 'as' => 'roles.', 'controller' => RolePermissionController::class], function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'edit')->name('edit');
        Route::put('/{id}/update', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('delete');
    });
    Route::group(['prefix' => 'users', 'as' => 'users.', 'controller' => UserController::class], function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('show/{id}', 'show')->name('show');
        Route::get('/{id}', 'edit')->name('edit');
        Route::post('/{id}/update', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('delete');
    });
});
