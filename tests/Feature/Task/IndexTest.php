<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
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
}
