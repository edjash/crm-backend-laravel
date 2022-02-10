<?php

use Illuminate\Support\Facades\Route;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/{any?}', function () {

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
