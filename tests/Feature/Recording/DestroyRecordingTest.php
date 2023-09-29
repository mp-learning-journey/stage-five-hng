<?php

namespace Tests\Feature\Recording;

use App\Models\Recording;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DestroyRecordingTest extends TestCase
{
    use RefreshDatabase;
    public function test_can_delete_recording()
    {
        // Create a test recording in the database
        $recording = Recording::factory()->create();

        $response = $this->deleteJson($this->url."/{$recording->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'statusCode',
            ]);

        $this->assertDatabaseMissing('recordings', ['id' => $recording->id]);
    }

    public function test_cannot_delete_nonexistent_recording()
    {
        $response = $this->deleteJson($this->url."/nonexistent_id");

        $response->assertStatus(404)
            ->assertJsonStructure([
                'error',
                'statusCode',
            ]);
    }
}
