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

        \Auth::login($user);
        $response = $this->get(route('task.show', ['task' => $task->id]));

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

    public function test_with_authentication_should_return_401(): void
    {
        $task = Task::factory()->create();

        $response = $this->get(route('task.show', ['task' => $task->id]));

        $response->assertUnauthorized();
    }

    public function test_user_should_not_be_able_to_view_others_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        \Auth::login($user);
        $response = $this->get(route('task.show', ['task' => $task->id]));

        $response->assertForbidden();
    }

    public function test_not_found(): void
    {
        $user = User::factory()->create();

        \Auth::login($user);
        $response = $this->get(route('task.show', ['task' => 10]));

        $response->assertNotFound();
    }
}
