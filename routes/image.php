<?php

use App\Http\Controllers\Api\ImagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [ImagesController::class, 'upload']);


// Route::get('emailConfirmation/{email}/{hash}', [ImagesController::class, 'verify']);
// Route::post('/login', [ImagesController::class, 'login'])->middleware('verifyEmail');
// Route::post('/forgotPassword', [ImagesController::class, 'forgotPassword']);
// Route::post('resetPassword/{email}/{hash}', [ImagesController::class, 'resetPassword']);

// Route::middleware(['jwtAuth'])->group(function () {
//     Route::post('/updateUser', [ImagesController::class, 'update']);
//     Route::post('/updatePassword', [ImagesController::class, 'updatePassword']);
//     Route::post('/logout', [ImagesController::class, 'logout']);
// });
