<?php

namespace Tests\Feature\Recording;

use App\Helpers\FileHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_nothing() {
        return true;
    }

//    public function test_can_create_recording_with_file_upload()
//    {
//        Storage::fake('videos'); // Use the videos folder in the disk for mocking file
//        Storage::fake('thumbnails');
//        $fileName = 'video.mp4';
//        $image = 'thumbnail.png';
//        $file = UploadedFile::fake()->create($fileName, 10240); // Create a fake video file (10MB)
//        $thumbnail = UploadedFile::fake()->image($image, 100, 100); // Create a fake thumbnail image
//
//        $response = $this->postJson($this->url, [
//            'file' => $file,
//            'isLastChunk' => 'true',
//        ]);
//
//        $response->assertStatus(201)
//            ->assertJsonStructure([
//                'data' => [
//                    'id',
//                    'title',
//                    'url',
//                    'transcription',
//                    'fileName',
//                    'fileSize',
//                    'thumbnail',
//                    'slug',
//                    'createdAt',
//                ],
//            ]);
//
//        // Verify that the file and thumbnail were stored
//        Storage::disk('public')->assertExists('videos/'.FileHelper::formatName($fileName));
//        Storage::disk('public')->assertExists('thumbnails/'.FileHelper::formatName($image));
//
//        // Verify that the recording was stored in the database
//        $this->assertDatabaseHas('recordings', [
//            'title' => 'Test Recording',
//        ]);
//    }

//    public function test_cannot_create_recording_with_missing_file()
//    {
//        $response = $this->postJson($this->url, [
//            'isLastChunk' => 'true',
//        ]);
//
//        $response->assertStatus(422);
//    }

//    public function test_cannot_create_recording_with_invalid_video_format()
//    {
//        $invalidFile = UploadedFile::fake()->create('document.pdf', 10240); // Create an invalid file (PDF)
//
//        $response = $this->postJson($this->url, [
//            'file' => $invalidFile,
//            'isLastChunk' => 'true',
//        ]);
//
//        $response->assertStatus(422);
//    }

//    public function test_cannot_create_recording_with_file_greater_than_20MB()
//    {
//        $largeFile = UploadedFile::fake()->create('large_video.mp4', 31200); // Create a large file (30MB)
//
//        $response = $this->postJson($this->url, [
//            'file' => $largeFile,
//            'isLastChunk' => 'true',
//        ]);
//
//        $response->assertStatus(422);
//    }
}
