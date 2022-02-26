<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'index']);

Route::get('/{any?}', function () {
    die("OK");
    $data = [
        "index_js" => "",
        "vendor_js" => "",
        "favicon" => ""
    ];

    $files = File::files(public_path('frontend'));

    foreach ($files as $file) {
        $fname = $file->getFilename();

        if (preg_match('/index\.(.*)\.js/', $fname)) {
            $data['index_js'] = $fname;
            continue;
        }
        if (preg_match('/vendor\.(.*)\.js/', $fname)) {
            $data['vendor_js'] = $fname;
            continue;
        }
        if (preg_match('/favicon\.(.*)\.svg/', $fname)) {
            $data['favicon'] = $fname;
            continue;
        }
    }

    return view('index', $data);
});
