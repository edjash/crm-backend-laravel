<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
 */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'index']);

Route::get('/{any?}', function () {

    $data = [
        "main_css",
        "main_js" => "",
        "vendor_js" => "",
        "favicon" => "",
    ];

    $files = File::files(public_path('static/js'));
    foreach ($files as $file) {
        $fname = $file->getFilename();
        $ext = substr(strrchr($fname, '.'), 1);
        if ((strpos($fname, 'main.') === 0) && ($ext === 'js')) {
            $data['main_js'] = '/static/js/' . $fname;
            continue;
        }
    }

    $files = File::files(public_path('static/css'));
    foreach ($files as $file) {
        $fname = $file->getFilename();
        $ext = substr(strrchr($fname, '.'), 1);
        if ((strpos($fname, 'main.') === 0) && ($ext === 'css')) {
            $data['main_css'] = '/static/css/' . $fname;
            continue;
        }
    }

    return view('index', $data);
});
