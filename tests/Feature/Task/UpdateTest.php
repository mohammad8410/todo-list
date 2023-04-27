<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;


    public function test_authorized_user_can_update_undone_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        $response = $this->actingAs($user)->put(route('task.update', ['task' => $task->id]), [
            'expires_at' => now(),
            'title' => 'ABCDE',
            'description' => 'dddd',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas(Task::class, [
            'expires_at' => now(),
            'title' => 'ABCDE',
            'description' => 'dddd',
        ]);
    }

    public function test_unauthorized_user_can_not_update_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->actingAs($user)->put(route('task.update', ['task' => $task->id]), [
            'expires_at' => now(),
            'title' => 'ABCDE',
            'description' => 'dddd',
        ]);

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'unauthorized access.',
        ]);
    }

    public function test_unauthenticated_user_can_not_update_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->put(route('task.update', ['task' => $task->id]), [
            'expires_at' => now(),
            'title' => 'ABCDE',
            'description' => 'dddd',
        ]);

        $this->assertGuest();
        $response->assertJson([
            'message' => 'unauthenticated user.',
        ]);
    }

    public function test_authorized_user_can_not_update_finished_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create(['done_at' => now()]);

        $response = $this->actingAs($user)->put(route('task.update', ['task' => $task->id]), [
            'expires_at' => now(),
            'title' => 'ABCDE',
            'description' => 'dddd',
        ]);

        $response->assertStatus(406);
        $response->assertJson([
            'message' => 'this task is finished.',
        ]);
    }

    public function test_resource_not_found_to_update()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('task.update', ['task' => 1]), [
            'expires_at' => now(),
            'title' => 'ABCDE',
            'description' => 'dddd',
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'message' => 'resource not found.',
        ]);
    }
}
