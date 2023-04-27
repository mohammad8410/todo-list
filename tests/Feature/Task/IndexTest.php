<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_data_structure(): void
    {
        $user = User::factory()->create();
        Task::factory()->withUser($user)->create();

        $response = $this->actingAs($user)->get(route('task.index'), [
            'user_id' => $user->id,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(
            [
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
                'data' => [
                    [
                        'id',
                        'title',
                        'description',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'expires_at',
                        'done_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ]
            ]
        );
    }

    public function test_unauthenticated_user_should_return_401(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('task.index'), [
            'user_id' => $user->id,
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'unauthenticated user.',
        ]);
    }

    public function test_filter_finished_tasks(): void
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory()->withUser($user)->create();
        Task::factory($expectedCount)->withUser($user)->create(['done_at' => now()]);

        $response = $this->actingAs($user)->get(route('task.index', [
            'user_id' => $user->id,
            'is_finished' => true,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_filter_unfinished_tasks(): void
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory($expectedCount)->withUser($user)->create();
        Task::factory()->withUser($user)->create(['done_at' => now()]);

        $response = $this->actingAs($user)->get(route('task.index', [
            'user_id' => $user->id,
            'is_finished' => false,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_filter_expired_tasks(): void
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory($expectedCount)->withUser($user)->create(['expires_at' => Carbon::yesterday()]);
        Task::factory()->withUser($user)->create();

        $response = $this->actingAs($user)->get(route('task.index', [
            'user_id' => $user->id,
            'is_expired' => true,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_filter_unexpired_tasks(): void
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory()->withUser($user)->create(['expires_at' => Carbon::yesterday()]);
        Task::factory($expectedCount)->withUser($user)->create();


        $response = $this->actingAs($user)->get(route('task.index', [
            'user_id' => $user->id,
            'is_expired' => false,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_index_pagination()
    {
        $per_page = 5;
        $page = 2;
        $user = User::factory()->create();
        $task = Task::factory($per_page * $page + 1)->withUser($user)->create();

        $response = $this->actingAs($user)->get(route('task.index', ['per_page' => $per_page, 'page' => $page]));

        $response->assertJsonCount($per_page, 'data');
    }
}
