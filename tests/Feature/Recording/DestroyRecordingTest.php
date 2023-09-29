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

        $this->assertSoftDeleted('recordings', ['id' => $recording->id]);
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

    public function test_cannot_delete_recording_on_error()
    {
        // Create a test recording in the database
        $recording = Recording::factory()->create();

        // Mock the database delete operation to throw an exception
        DB::shouldReceive('delete')
            ->once()
            ->with('DELETE FROM recordings WHERE id = ?', [$recording->id])
            ->andThrow(new \Exception('Database error'));

        $response = $this->deleteJson($this->url."/{$recording->id}");

        $response->assertStatus(500)
            ->assertJsonStructure([
                'error',
                'statusCode',
            ]);
    }


}
