<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

trait AvatarTrait
{
    public function uploadAvatar(Request $request)
    {
        if (!is_writable(storage_path('app/public/avatars/tmp'))) {
            return response()->json([
                "error" => "No filesystem permission to store temporary avatar.",
            ], 500);
        }

        Validator::make($request->file(), [
            'avatar' => 'mimes:jpeg,jpg,png,gif|max:10000',
        ])->validate();

        $file = $request->file('avatar');
        $hashName = 'tmp_' . $file->hashName();
        $path = $file->storePubliclyAs('public/avatars/tmp', $hashName);

        return response()->json(["filename" => basename($path)]);
    }

    public function saveAvatar($file): string
    {
        if (!$file || (substr($file, 0, 4) !== 'tmp_')) {
            return $file;
        }

        if (!is_writable(storage_path('app/public/avatars'))) {
            Log::error(storage_path('app/public/avatars') . ' is not writeable');
            return '';
        }

        $tmpfile = 'public/avatars/tmp/' . $file;
        $newfile = str_replace('tmp_', '', $file);
        if (!Storage::exists($tmpfile)) {
            return '';
        }

        $targets = [
            ['path' => 'public/avatars/large/', 'width' => 500, 'height' => 500],
            ['path' => 'public/avatars/medium/', 'width' => 100, 'height' => 100],
            ['path' => 'public/avatars/small/', 'width' => 40, 'height' => 40],
        ];

        foreach ($targets as $target) {
            $fname = basename($newfile);
            $dst = $target['path'] . $fname;
            //resize new image
            if ($target['width'] && $target['height']) {
                $xsrc = storage_path('app/' . $tmpfile);
                $xdst = storage_path('app/' . $dst);
                $img = Image::make($xsrc);
                $img->resize($target['width'], $target['height']);
                $img->save($xdst);
            } else {
                Storage::copy($tmpfile, $dst);
            }
        }

        Storage::delete($tmpfile);

        return $newfile;
    }
}
