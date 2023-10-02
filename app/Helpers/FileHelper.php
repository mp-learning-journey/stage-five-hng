<?php

namespace App\Helpers;

use App\Models\Recording;
use FFMpeg\FFMpeg;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function formatName($filename, $id = null): string
    {
        return $id . '_' . str_replace(' ', '_', strtolower($filename));
    }

    /**
     * store file in storage and return file name
     * @param UploadedFile $file
     * @param string $folderName folder name to store file. Leave empty to store in public folder
     * @return bool|object
     */
    public static function upload($request, string $folderName = '', string $id = null)
    {
        $isLastChunk = $request->input('isLastChunk') === 'true';

        try {
            $file = $request->file('file');
            $name = self::formatName($file->getClientOriginalName(), $id);
            $size = $file->getSize();

            $fileName = $folderName . "/" . $name;
            $outputPath = storage_path('app/public/' . $fileName);
            $savePath = storage_path('app/public/videos/saved.mp4');

//            if the file exists, create a new file

            if (file_exists($outputPath)) {
                $tempFile = "temp_recording/" . $name;
                $tempPath = storage_path('app/public/' . $tempFile);
                Storage::disk('public')->put($tempFile, file_get_contents($file));

                // Initialize FFmpeg
                $ffmpeg = FFMpeg::create();

                // Open the first video file
                $existingVideo = $ffmpeg->open($outputPath);

                // Concatenate the videos
                if($existingVideo->concat([$outputPath, $tempPath])->saveFromSameCodecs($savePath)){
                    // Delete the new chunk file
                    unlink($tempPath);

                    unlink($outputPath); // delete previous video
                    rename($savePath, $outputPath); // rename new video as previous video
                }

            } else {
                // Store the video in the 'public' disk
                Storage::disk('public')->put($fileName, file_get_contents($file));
            }

            if ($isLastChunk) {
                return (object)[
                    'file' => $fileName,
                    'fileName' => $name,
                    'fileSize' => $size,
                    'completed' => true
                ];
            } else {
                return (object)['completed' => false];
            }
        }
        catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public static function transcribeVideo(Recording $recording, $file)
    {
        $url = "https://transcribe.whisperapi.com";
        $api_key = env('WHISPER_API_KEY');

        // Prepare the data for the transcription request
        $data = [
            "fileType" => "mp4",
            "diarization" => "false",
            "file" => $file,
            "language" => "en",
            "task" => "transcribe"
        ];

        // Send the transcription request using Laravel's HTTP client
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $api_key])->post($url, $data);
        if ($response->successful()) {
            echo $response->json('text');
            $recording->description = $response->json('text');
            $recording->save();
            return true;
        } else {
            $errorMessage = $response->json('message');
            Log::error("Transcription request failed. Error message: $errorMessage");
        }
    }
}


