<?php

namespace Tests\Feature\Task;

use App\Events\TaskDone;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_should_be_able_to_mark_his_task_as_done(): void
    {
        $user = User::factory()->create();
        $task1 = Task::factory()->withUser($user)->create();
        $task2 = Task::factory()->withUser($user)->create();

        $response = $this->actingAs($user)->post(route('task.done', ['task' => $task1->id]));

        $response->assertOk();
        $this->assertDatabaseHas(Task::class, [
            'id' => $task1->id,
            'done_at' => now(),
        ]);
    }

    public function test_user_should_only_be_allowed_to_done_his_own_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->actingAs($user)->post(route('task.done', ['task' => $task->id]));

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'unauthorized access.',
        ]);
    }

    public function test_expired_tasks_can_not_be_marked_as_done(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();
        $task->update(['expires_at' => Carbon::yesterday()]);

        $response = $this->actingAs($user)->post(route('task.done', ['task' => $task->id]));

        $response->assertJson([
            'message' => 'The task is expired.',
        ]);
    }

    public function test_unauthenticated_user_can_not_access_to_done_method(): void
    {
        $task = Task::factory()->create();

        $response = $this->post(route('task.done', ['task' => $task->id]));

        $response->assertJson([
            'message' => 'unauthenticated user.',
        ]);
        $this->assertGuest();
    }

    public function test_user_score_will_increased_when_task_is_done()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create(['done_at' => now()]);
        TaskDone::dispatch($task);
        $task = Task::factory()->withUser($user)->create(['done_at' => now()]);
        TaskDone::dispatch($task);

        $this->assertDatabaseHas(User::class, [
            'score' => 10,
        ]);
    }
}
