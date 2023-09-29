<?php

namespace Tests\Feature\Recording;

use App\Models\Recording;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowRecordingTest extends TestCase
{
    use RefreshDatabase;
    public function test_can_show_recording()
    {
    // Create a test recording in the database
    $recording = Recording::factory()->create();

    $response = $this->getJson($this->url."/{$recording->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'url',
                'description',
                'fileName',
                'fileSize',
                'thumbnail',
                'slug',
                'createdAt',
            ],
        ])
        ->assertJsonFragment([
            'id' => $recording->id,
            'title' => $recording->title,
        ]);
    }

    public function test_cannot_show_nonexistent_recording()
    {
        $response = $this->getJson($this->url."/nonExistentId}");

        $response->assertStatus(404)
            ->assertJsonStructure([
                'error',
                'statusCode',
            ]);
    }
}
