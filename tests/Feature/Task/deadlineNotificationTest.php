<?php

namespace Tests\Feature\Task;

use App\Jobs\NotifyOneDayDeadline;
use App\Mail\DeadlineNotificationMail;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class deadlineNotificationTest extends TestCase
{
    use RefreshDatabase;


    public function test_deadline_mail()
    {
        \Mail::fake();
        $user = User::factory()->create();
        $task = Task::factory()->withUser($user)->create(['expires_at' => Carbon::now()->addDay()]);
        $this->travel(1)->hours();

        NotifyOneDayDeadline::dispatch();

        \Mail::assertSent(DeadlineNotificationMail::class, function (DeadlineNotificationMail $mail) use ($user, $task) {
            return $mail->hasTo($user->email) &&
                $mail->assertSeeInHtml($user->name) &&
                $mail->assertSeeInHtml($task->title) &&
                $mail->assertSeeInHtml($task->description);
        });
    }
}
