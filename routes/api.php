<?php
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\IndustryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
 */

Route::get('/countries', [CountriesController::class, 'index'])->middleware('auth:sanctum');
Route::get('/industries', [IndustryController::class, 'index'])->middleware('auth:sanctum');

Route::controller(ContactController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', 'index');
    Route::post('/contacts/avatar', 'uploadAvatar');
    Route::get('/contacts/{id}', 'getContact');
    Route::post('/contacts/{id}', 'update');
    Route::post('/contacts', 'create');
    Route::delete('/contacts/{deleteIds}', 'delete');
});

Route::controller(CompanyController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/companies', 'index');
    Route::post('/companies/avatar', 'uploadAvatar');
    Route::get('/companies/{id}', 'getCompany');
    Route::post('/companies/{id}', 'update');
    Route::post('/companies', 'create');
    Route::delete('/companies/{deleteIds}', 'delete');
});

Route::controller(NotesController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/notes/{contactType}/{contactId}', 'index');
});
