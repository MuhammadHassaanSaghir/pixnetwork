<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::get('EmailConfirmation/{email}/{hash}', [UserController::class, 'verify']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware(['myauths'])->group(function () {
    Route::post('/UpdateUser', [UserController::class, 'update']);
    Route::post('/UpdatePassword', [UserController::class, 'update_password']);
    Route::post('/logout', [UserController::class, 'logout']);
});
