<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Task;

class TaskApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    public function test_create_task_validation_failure(): void
    {
        $res = $this->postJson('/api/tasks', [
            'description' => 'Missing title',
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $res = $this->getJson('/api/tasks');

        $res->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'status', 'due_date', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_update_task(): void
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $res = $this->putJson("/api/tasks/{$task->id}", [
            'status' => 'completed',
        ]);

        $res->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    public function test_delete_task(): void
    {
        $task = Task::factory()->create();

        $res = $this->deleteJson("/api/tasks/{$task->id}");

        $res->assertNoContent();

        // because we used soft deletes
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
