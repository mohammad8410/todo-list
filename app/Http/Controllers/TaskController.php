<?php

namespace App\Http\Controllers;

use App\Http\Controllers\pagination\Pagination;
use App\Http\Responses\TaskResponse;
use App\Models\Task;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('index', $request->user_id);
        $user = Auth::user();
        $userTasks = Task::query()->where('user_id', '=', $user->id);
        if ($request->is_finished) {
            $userTasks->where('done_at', 'NOT IS NULL', null);
        }
        if ($request->is_expired) {
            $userTasks->where('expires_at', '<', now());
        }
        $userTasks = $userTasks->paginate(15);

        return Response::json(Pagination::fromModelPaginatorAndData(
            $userTasks,
            collect($userTasks->items())->map(fn($item) => new TaskResponse($item))->toArray()
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create-task');
        $validationFlag = $request->validate([
            'description' => 'required',
            'expires_at' => 'required',
        ]);
        $user = Auth::user();
        $newTask = Task::create([
            'user_id' => $user->id,
            'description' => $request->input('description'),
            'expires_at' => $request->input('expires_at'),
        ]);
        return response(new TaskResponse($newTask), 201);
    }

    public function show(Task $task)
    {
        $this->authorize('tasks', $task);

        return new TaskResponse($task);
    }

    public function done(Task $task)
    {
        $this->authorize('tasks', $task);
        if (\Carbon\Carbon::now()->lte($task->expires_at)) {
            $task->update([
                'done_at' => now()
            ]);
            return Response::json(new TaskResponse($task));
        }
        return response([
            "message" => "The task is expired."
        ], 400);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('tasks', $task);
        $validationFlag = $request->validate([
            'expires_at' => 'required',
            'description' => 'required',
        ]);
        if (!$task->done_at) {
            $task->update([
                'expires_at' => $request->input('expires_at'),
                'description' => $request->input('description'),
            ]);
            return new TaskResponse($task);
        }
        return response([
            "message" => "this task is finished."
        ], 400);
    }
}
