<?php

use Illuminate\Support\Facades\Route;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    Mail::to('edjash@gmail.com')->send(new ResetPassword());
    die("XYZ");
});
