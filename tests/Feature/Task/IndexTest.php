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

    public function test_valid_data_structure_returned_by_index_method()
    {
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create();

        \Auth::login($user);
        $response = $this->get(route('task.index'), [
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

    public function test_unauthenticated_user_should_return_401_from_index_method()
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

    public function test_user_wants_to_get_finished_tasks()
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory()->withUser($user)->create();
        Task::factory($expectedCount)->withUser($user)->create(['done_at' => now()]);

        \Auth::login($user);
        $response = $this->get(route('task.index', [
            'user_id' => $user->id,
            'is_finished' => true,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_user_wants_to_get_unfinished_tasks()
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory($expectedCount)->withUser($user)->create();
        Task::factory()->withUser($user)->create(['done_at' => now()]);

        \Auth::login($user);
        $response = $this->get(route('task.index', [
            'user_id' => $user->id,
            'is_finished' => false,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_user_wants_to_get_expired_tasks()
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory($expectedCount)->withUser($user)->create(['expires_at' => Carbon::yesterday()]);
        Task::factory()->withUser($user)->create();

        \Auth::login($user);
        $response = $this->get(route('task.index', [
            'user_id' => $user->id,
            'is_expired' => true,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_user_wants_to_get_unexpired_tasks()
    {
        $expectedCount = 2;
        $user = User::factory()->create();
        Task::factory()->withUser($user)->create(['expires_at' => Carbon::yesterday()]);
        Task::factory($expectedCount)->withUser($user)->create();

        \Auth::login($user);
        $response = $this->get(route('task.index', [
            'user_id' => $user->id,
            'is_expired' => false,
        ]));

        $response->assertOk();
        $response->assertJsonCount($expectedCount, 'data');
    }
}
