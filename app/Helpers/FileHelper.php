<?php

namespace App\Helpers;

use App\Models\Recording;
use FFMpeg\Coordinate\TimeCode;
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
            $fileType = pathinfo($outputPath, PATHINFO_EXTENSION);
            $savePath = storage_path("app/public/videos/saved." . $fileType);

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

    public static function transcribeVideo(Recording $recording)
    {
        $url = "https://transcribe.whisperapi.com";
        $api_key = env('WHISPER_API_KEY');
        $file = asset('storage/'. $recording->file_location);

        $fileType = pathinfo($file, PATHINFO_EXTENSION);
        // Prepare the data for the transcription request
        $data = [
            "fileType" => $fileType,
            "diarization" => "false",
            "language" => "en",
            "task" => "transcribe",
        ];

        // Send the transcription request using Laravel HTTP client
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $api_key])
            ->attach('file', file_get_contents($file), 'video.mp4')->post($url, $data);
        if ($response->successful()) {
            $segments = $response->json('segments');

            // save full transcriptions
            $recording->description = $response->json('text');
            $recording->save();

            self::transcribeInSegment($recording, $segments);

            return true;
        } else {
            $errorMessage = $response->json('message');
            Log::error("Transcription request failed. Error message: $errorMessage");
            return false;
        }
    }

    public static function transcribeInSegment($recording, $segments): void
    {
        $x = 1;
        foreach ($segments as $segment) {
            $recording->transcriptions()->create([
                'position' => $x,
                'start' => $segment['start'],
                'end' => $segment['end'],
                'description' => $segment['text']
            ]);
            $x++;
        }
    }

    public static function generateThumbnail($videoPath, $name = '', $time = '00:00:05')
    {
        // Initialize FFmpeg
        $ffmpeg = FFMpeg::create();

        // Open the video file
        $video = $ffmpeg->open($videoPath);

        // Set the time (in HH:MM:SS format) for the thumbnail capture
        $timecode = new TimeCode($time);
        $name = 'thumbnails/'. $name;
        $thumbnailPath = storage_path('app/public/');

        // Generate the thumbnail
        $video->frame($timecode)->save($thumbnailPath);
    }
}



