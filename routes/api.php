<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'index']);
Route::get('/contacts', [ContactController::class, 'index'])->middleware('auth:sanctum');
Route::delete('/contacts/{ids}', [ContactController::class, 'delete'])->middleware('auth:sanctum');
Route::get('/companies', [CompanyController::class, 'index'])->middleware('auth:sanctum');
Route::delete('/companies/{ids}', [CompanyController::class, 'delete'])->middleware('auth:sanctum');
