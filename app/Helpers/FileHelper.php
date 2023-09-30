<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileHelper {
    public static function formatName($filename, $id = null): string {
        return $id . '_' .str_replace(' ','_', strtolower($filename));
    }

    /**
     * store file in storage and return file name
     * @param UploadedFile $file
     * @param string $folderName folder name to store file. Leave empty to store in public folder
     * @return bool|object
     */
    public static function upload($file, string $folderName = '', string $id = null) {
        try{

            $name = $file->getClientOriginalName(); // name of uploaded file
            $size = $file->getSize(); // size in bytes
            $fileName = $folderName."/" . self::formatName($name);


            // Store the video in the 'public' disk (storage/app/public)
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

//    private function storeUpload( Request $request ){
//        $data = str_replace("data:audio/mp3;base64,","",$request->data);
//        Storage::put('file.mp3', base64_decode($data), 'public');
//    }
}


