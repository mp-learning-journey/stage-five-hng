<?php

namespace Tests\Feature\Recording;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_recordings()
    {
        // Create some test recordings in the database
        $recording1 = Recording::factory()->create();
        $recording2 = Recording::factory()->create();

        $response = $this->getJson('/recordings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
                'links',
                'meta',
            ])
            ->assertJsonFragment([
                'id' => $recording1->id,
                'title' => $recording1->title,
            ])
            ->assertJsonFragment([
                'id' => $recording2->id,
                'title' => $recording2->title,
            ]);
    }

    public function test_can_list_recordings_empty()
    {
        $response = $this->getJson('/recordings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
                'links',
                'meta',
            ]);
    }

    public function test_cannot_list_recordings_on_error()
    {
        // Mock an error or exception scenario
        // For example, create a scenario where the database connection is unavailable

        $this->mockDatabaseError(); // Implement your custom mock

        $response = $this->getJson('/recordings');

        $response->assertStatus(500)
            ->assertJsonStructure([
                'error',
                'statusCode',
            ]);
    }
}
