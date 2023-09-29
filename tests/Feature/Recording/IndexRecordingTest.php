<?php

namespace Tests\Feature\Recording;

use App\Models\Recording;
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

        $response = $this->getJson($this->url);

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
        $response = $this->getJson($this->url);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
                'links',
                'meta',
            ]);
    }

}
