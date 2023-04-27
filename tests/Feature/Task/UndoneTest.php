<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UndoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_mark_a_task_as_undone()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create(['done_at' => Carbon::now()]);

        $response = $this->actingAs($user)->post(route('task.undone', ['task' => $task->id]));

        $response->assertOk();
        $this->assertDatabaseHas(Task::class, [
            'done_at' => null,
        ]);
    }

    public function test_unauthenticated_user_can_not_mark_a_task_as_undone()
    {
        $task = Task::factory()->create(['done_at' => now()]);

        $response = $this->post(route('task.undone', ['task' => $task->id]));

        $this->assertGuest();
    }

    public function test_unauthorized_user_can_not_mark_a_task_as_undone()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['done_at' => now()]);

        $response = $this->actingAs($user)->post(route('task.undone', ['task' => $task->id]));

        $response->assertForbidden();
    }
}
