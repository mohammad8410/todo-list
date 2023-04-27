<?php

namespace App\Listeners;

use App\Events\TaskDone;
use App\Models\User;

class IncreaseUserScore
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
    public function handle(TaskDone $event): void
    {
        User::query()->where('id', '=', $event->userId)->increment('score', 5);
    }
}
