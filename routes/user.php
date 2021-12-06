<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::get('emailConfirmation/{email}/{hash}', [UserController::class, 'verify']);
Route::post('/login', [UserController::class, 'login'])->middleware('verifyEmail');
Route::post('/forgotPassword', [UserController::class, 'forgotPassword']);
Route::post('resetPassword/{email}/{hash}', [UserController::class, 'resetPassword']);

Route::middleware(['jwtAuth'])->group(function () {
    Route::post('/updateUser', [UserController::class, 'update']);
    Route::post('/updatePassword', [UserController::class, 'updatePassword']);
    Route::post('/logout', [UserController::class, 'logout']);
});
