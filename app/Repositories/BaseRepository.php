<?php

namespace App\Repositories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BaseRepository
{
    public function uploadImage($folder, $image)
    {
        $imageName = Str::random(6) . time() . '.' . $image->extension();
        // $path = Storage::disk('public')->put($folder . '/' . $imageName, file_get_contents($image));
        $path = $image->storeAs(
            $folder,
            $imageName,
            'public'
        );
        return $path;
    }
    public function deleteImage($folder, $image)
    {
        return $image ? Storage::disk('public')->delete($folder .'/' . $image) : '';
    }
}
