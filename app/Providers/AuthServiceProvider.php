<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::define('tasks', function (User $user, Task $task) {
            return $user->id === $task->user_id;
        });

        Gate::define('create-task', function (User $user) {
            return true;
        });

        Gate::define('viewAny-task', function (User $user) {
            return $user->id === 1;
        });

        Gate::define('index', function (User $user, $userId) {
            return $user->id == $userId;
        });
        //
    }
}
