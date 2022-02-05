<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CountriesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'index']);
Route::get('/countries', [CountriesController::class, 'index']);

Route::controller(ContactController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', 'index');
    Route::post('/contacts/avatar/{id?}', 'avatar');
    Route::get('/contacts/{id}', 'getContact');
    Route::post('/contacts/{id}', 'update');
    Route::post('/contacts', 'create');
    Route::delete('/contacts/{deleteIds}', 'delete');
});

Route::get('/companies', [CompanyController::class, 'index'])->middleware('auth:sanctum');
Route::post('/companies', [CompanyController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/companies/{deleteIds}', [CompanyController::class, 'delete'])->middleware('auth:sanctum');
