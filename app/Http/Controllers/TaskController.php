<?php

namespace App\Http\Controllers;

use App\Http\Controllers\pagination\Pagination;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Responses\TaskResponse;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class TaskController extends Controller
{
    public function index(IndexTaskRequest $request)
    {
        $canViewAllTasks = false;
        if (Gate::allows('viewAny-task')) {
            $canViewAllTasks = true;
        }

        $user = Auth::user();
        $userId = $canViewAllTasks ? $request->get('user_id') : $user->id;

        $taskQuery = Task::query();

        if ($userId !== null) {
            $taskQuery->where('user_id', '=', $userId);
        }

        $isFinished = $request->get('is_finished');

        if ($isFinished !== null) {
            if ($isFinished) {
                $taskQuery->whereNotNull('done_at');
            } else {
                $taskQuery->whereNull('done_at');
            }
        }

        $isExpired = $request->get('is_expired');

        if ($isExpired !== null) {
            if ($isExpired) {
                $taskQuery->where('expires_at', '<', now());
            } else {
                $taskQuery->whereNot('expires_at', '<', now());
            }
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $taskQuery = $taskQuery->paginate(perPage: $perPage, page: $page);

        return Response::json(Pagination::fromModelPaginatorAndData(
            $taskQuery,
            collect($taskQuery->items())->map(fn($item) => new TaskResponse($item))->toArray()
        ));
    }

    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create-task');

        $newTask = Task::create(
            [
                'user_id' => Auth::user()->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'expires_at' => $request->input('expires_at'),
            ]
        );

        return response(new TaskResponse($newTask), 201);
    }

    public function show(Task $task)
    {
        $this->authorize('tasks', $task);

        return response(new TaskResponse($task), 200);
    }

    public function done(Task $task)
    {
        $this->authorize('tasks', $task);

        if (\Carbon\Carbon::now()->lte($task->expires_at)) {
            $task->update([
                'done_at' => now()
            ]);
            return response(new TaskResponse($task), 200);
        }

        return response([
            "message" => "The task is expired."
        ], 406);
    }

    public function undone(Task $task)
    {
        $this->authorize('tasks', $task);
        $task->update([
            'done_at' => null,
        ]);
        return response(new TaskResponse($task), 200);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('tasks', $task);
        $validationFlag = $request->validate([
            'expires_at' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);
        if (!$task->done_at) {
            $task->update([
                'expires_at' => $request->input('expires_at'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
            ]);
            return response(new TaskResponse($task), 200);
        }
        return response([
            "message" => "this task is finished."
        ], 406);
    }

    public function delete(Task $task)
    {
        $this->authorize('tasks', $task);

        $task->delete();

        return response(new TaskResponse($task), 200);
    }
}
