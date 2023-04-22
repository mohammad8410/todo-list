<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_a_task(): void
    {
        $user = User::factory()->create();

        \Auth::login($user);
        $response = $this->post(route('task.store'), [
            'user_id' => $user->id,
            'title' => 'ABCDE',
            'description' => 'dddd',
            'expires_at' => Carbon::tomorrow(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas(Task::class, [
            'user_id' => $user->id,
            'title' => 'ABCDE',
            'description' => 'dddd',
            'expires_at' => Carbon::tomorrow(),
        ]);
    }

    public function test_unauthenticated_user_can_not_store_a_task(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('task.store'), [
            'user_id' => $user->id,
            'title' => 'AAA',
            'description' => 'BBB',
            'expires_at' => Carbon::tomorrow(),
        ]);

        $this->assertGuest();
        $response->assertJson([
            'message' => 'unauthenticated user.',
        ]);
    }
}
