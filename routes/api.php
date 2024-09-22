<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'api.'], function () {
    Route::prefix('auth')
        ->group(function () {
            Route::post('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/login', [AuthController::class, 'login'])->name('login');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
        });

    Route::prefix('events')
        ->middleware('auth:sanctum')
        ->group(function () {
            Route::post('get', [EventsController::class, 'all'])->name('events.get');
            Route::post('create', [EventsController::class, 'store'])->name('events.create');
            Route::post('find', [EventsController::class, 'find']);
            Route::post('join', [EventsController::class, 'join'])->name('events.join');
            Route::put('update', [EventsController::class, 'update']);
            Route::delete('delete', [EventsController::class, 'destroy']);
            Route::post('future', [EventsController::class, 'future'])->name('events.future');
            Route::post('filter', [EventsController::class, 'filter'])->name('events.filter');
        });
    Route::prefix('users')
        ->middleware('auth:sanctum')
        ->group(function(){
            Route::get('/get', [UsersController::class, 'index'])->name('users.get');
        });
});
