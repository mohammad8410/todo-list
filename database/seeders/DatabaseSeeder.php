<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        \App\Models\User::factory(10)->create()->each(function ($user){
//            Task::factory(3)->create([
//                'user_id' => $user->id,
//            ]);
//        });
        User::create([
            'name' => 'moz',
            'email' => 'moz@gmail.com',
            'password' => Hash::make('11110000'),
        ])->each(function ($user) {
            Task::create([
                'user_id' => $user->id,
                'description' => 'math',
                'expires_at' => fake()->dateTimeBetween('now', '+1 week', null),
            ]);
        });
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
