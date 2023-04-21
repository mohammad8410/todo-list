<?php

namespace App\Http\Responses;

use App\Models\Task;

class TaskResponse extends \Spatie\LaravelData\Data
{
    public int $id;
    public string $title;
    public string $description;
    public int $user_id;
    public int $created_at;
    public int $updated_at;
    public int $expires_at;
    public ?int $done_at;

    public function __construct(Task $task)
        // public function __construct($task)
    {
        $this->id = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->user_id = $task->user_id;
        $this->created_at = $task->created_at->timestamp;
        $this->updated_at = $task->updated_at->timestamp;
        $this->expires_at = $task->expires_at->timestamp;
        $this->done_at = $task->done_at?->timestamp;
    }
}
