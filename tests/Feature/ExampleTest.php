<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AAA -> Arrange, Act, Assert
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_should_be_able_to_mark_his_task_as_done(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        \Auth::login($user);
        $response = $this->post(route('task.done', ['task' => $task->id]));

        $response->assertOk();

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
            'done_at' => now(),
        ]);
    }

    public function test_user_should_only_be_allowed_to_done_his_own_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        \Auth::login($user);
        $response = $this->post(route('task.done', ['task' => $task->id]));

        $response->assertForbidden();
    }
}
