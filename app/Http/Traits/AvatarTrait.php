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
        $tmpname = $this->saveTmpAvatar($file);
        return response()->json(["filename" => $tmpname]);
    }

    public function saveTmpAvatar($file): string
    {
        if (!is_writable(storage_path('app/public/avatars'))) {
            Log::error(storage_path('app/public/avatars') . ' is not writeable');
            return '';
        }
        if (!is_writable(storage_path('app/public/avatars/tmp'))) {
            Log::error(storage_path('app/public/avatars/tmp') . ' is not writeable');
            return '';
        }

        $tmpname = 'tmp_' . $file->hashName();
        if (!$file->storePubliclyAs('public/avatars/tmp', $tmpname)) {
            return '';
        }

        $targets = [
            ['path' => 'app/public/avatars/large/', 'width' => 500, 'height' => 500],
            ['path' => 'app/public/avatars/medium/', 'width' => 100, 'height' => 100],
            ['path' => 'app/public/avatars/small/', 'width' => 40, 'height' => 40],
        ];

        foreach ($targets as $target) {
            //resize new image
            $src = storage_path('app/public/avatars/tmp/' . $tmpname);
            $img = Image::make($src);
            $img->resize($target['width'], $target['height']);
            $img->save(storage_path($target['path'] . $tmpname));
        }

        return $tmpname;
    }

    public function savePermAvatar($tmpname): string
    {
        if (!$tmpname || strpos($tmpname, 'tmp_') !== 0) {
            return $tmpname;
        }

        $paths = [
            'public/avatars/large/',
            'public/avatars/medium/',
            'public/avatars/small/',
        ];

        $fname = str_replace('tmp_', '', $tmpname);
        foreach ($paths as $path) {
            $src = $path . $tmpname;
            $dst = $path . $fname;
            if (!Storage::move($src, $dst)) {
                return '';
            }
        }

        Storage::delete(storage_path('app/public/avatars/tmp' . $tmpname));

        return $fname;
    }
}
