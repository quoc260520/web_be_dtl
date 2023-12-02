<?php

namespace App\Repositories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BaseRepository
{
    public function uploadImage($image)
    {
        $imageName = Str::random(6) . time() . '.' . $image->extension();
        Storage::disk('public')->put('avatar/' . $imageName, file_get_contents($image));
        return $imageName;
    }
    public function deleteImage($imageName)
    {
        return $imageName ? Storage::disk('public')->delete('avatar/' . $imageName) : '';
    }
}
