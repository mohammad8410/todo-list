<?php

namespace App\Listeners;

use App\Events\TaskUndone;
use App\Models\User;

class DecreaseUserScore
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskUndone $event): void
    {
        User::query()->where('id', '=', $event->userId)->decrement('score', 5);
    }
}
