<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_delete_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        \Auth::login($user);
        $response = $this->delete(route('task.delete', ['task' => $task]));

        $response->assertOk();
        $this->assertDatabaseHas(Task::class, [
            'deleted_at' => now(),
        ]);
        $response->assertJsonStructure([
            'id',
            'title',
            'description',
            'user_id',
            'created_at',
            'updated_at',
            'expires_at',
            'done_at'
        ]);
    }

    public function test_unauthorized_user_can_not_delete_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        \Auth::login($user);
        $response = $this->delete(route('task.delete', ['task' => $task]));

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'unauthorized access.',
        ]);
    }

    public function test_unauthenticated_user_can_not_delete_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->delete(route('task.delete', ['task' => $task]));

        $this->assertGuest();
        $response->assertJson([
            'message' => 'unauthenticated user.',
        ]);
    }

    public function test_resource_not_found_to_delete()
    {
        $user = User::factory()->create();

        \Auth::login($user);
        $response = $this->delete(route('task.delete', ['task' => 1]));

        $response->assertNotFound();
        $response->assertJson([
            'message' => 'resource not found.',
        ]);
    }
}
