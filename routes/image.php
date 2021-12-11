<?php

use App\Http\Controllers\Api\ImagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/uploadImage', [ImagesController::class, 'upload']);
// Below middleare is comment because i try to check this image is actually of login user. But failed because we cannot pass Route Params to Middleware 
// Route::middleware(['accessImage:{id}'])->group(function ($id) {
Route::delete('/deleteImage/{id}', [ImagesController::class, 'remove']);
Route::get('/fetchImage', [ImagesController::class, 'fetch']);
Route::post('/changePrivacy/{id}', [ImagesController::class, 'changePrivacy']);
Route::post('/searchImage', [ImagesController::class, 'searchImage']);
Route::post('/shareLink/{id}', [ImagesController::class, 'shareLink']);
// });
Route::get('view/{id}', [ImagesController::class, 'view']);
