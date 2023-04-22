<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoneTest extends TestCase
{
    use RefreshDatabase;

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

        $response->assertJson([
            'message' => 'unauthorized access.',
        ]);
    }

    public function test_expired_tasks_can_not_mark_as_done()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();
        $task->update(['expires_at' => "2020-04-25 23:19:44"]);

        \Auth::login($user);
        $response = $this->post(route('task.done', ['task' => $task->id]));

        $response->assertJson([
            'message' => 'The task is expired.',
        ]);
    }

    public function test_unauthenticated_user_can_not_access_to_done_method()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        $response = $this->post(route('task.done', ['task' => $task->id]));

        $response->assertJson([
            'message' => 'unauthenticated user.',
        ]);
        $this->assertGuest();
    }
}
