<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_data_structure(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        $response = $this->actingAs($user)->get(route('task.show', ['task' => $task->id]));

        $response->assertOk();
        $response->assertJsonStructure(
            [
                'id',
                'title',
                'description',
                'user_id',
                'created_at',
                'updated_at',
                'expires_at',
                'done_at',
            ]
        );
    }

    public function test_success_data(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        $response = $this->actingAs($user)->get(route('task.show', ['task' => $task->id]));

        $response->assertOk();
        $response->assertJson(
            [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'user_id' => $task->user_id,
                'created_at' => $task->created_at->timestamp,
                'updated_at' => $task->created_at->timestamp,
                'expires_at' => $task->expires_at->timestamp,
                'done_at' => null,
            ]
        );
    }

    public function test_without_authentication_should_return_401(): void
    {
        $task = Task::factory()->create();

        $response = $this->get(route('task.show', ['task' => $task->id]));

        $response->assertUnauthorized();
        $response->assertJson([
            "message" => "unauthenticated user.",
        ]);
    }

    public function test_user_should_not_be_able_to_view_others_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->actingAs($user)->get(route('task.show', ['task' => $task->id]));

        $response->assertForbidden();
        $response->assertJson([
            "message" => "unauthorized access.",
        ]);
    }

    public function test_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('task.show', ['task' => 10]));

        $response->assertNotFound();
        $response->assertJson([
            'message' => 'resource not found.',
        ]);
    }
}
