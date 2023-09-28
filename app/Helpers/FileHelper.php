<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileHelper {
    public static function formatName($filename): string {
        return time() . '_' .str_replace(' ','_', strtolower($filename));
    }

    /**
     * store file in storage and return file name
     * @param UploadedFile $file
     * @param string $folderName folder name to store file. Leave empty to store in public folder
     * @return bool|object
     */
    public static function upload(UploadedFile $file, string $folderName = '') {
        try{
            $name = $file->getClientOriginalName(); // name of uploaded file
            $size = $file->getSize(); // size in bytes
            $fileName = $folderName."/" . self::formatName($name);

            // Store the résumé in the 'public' disk (storage/app/public)
            Storage::disk('public')->put($fileName, file_get_contents($file));

            return (object)[
                'file' => $fileName,
                'fileName' => $name,
                'fileSize' => $size
            ];
        }
        catch (\Exception $e) {
            return false;
        }

    }
}

