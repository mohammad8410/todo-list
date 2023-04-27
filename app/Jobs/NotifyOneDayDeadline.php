<?php

namespace App\Jobs;

use App\Mail\DeadlineNotificationMail;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyOneDayDeadline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tasks = Task::with('user')->where('notified', '=', 0)
            ->whereNull('done_at')
            ->where('expires_at', '>', Carbon::tomorrow())
            ->get();

        $tasks->each(function ($task) {
            assert($task instanceof Task);
            Mail::to($task->user)->send(new DeadlineNotificationMail($task));
            $task->update(['notified' => 1]);
        });

    }
}
