<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['web']], function () {

    Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
        return $request->user();
    });
    require __DIR__ . '/auth.php';


    Route::get('/tasks', [TaskController::class, 'index'])
        ->middleware('auth')
        ->name('task.index');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->middleware('auth')
        ->name('task.show');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('auth')
        ->name('task.store');

    Route::post('/tasks/{task}/done', [TaskController::class, 'done'])
        ->middleware('auth')
        ->name('task.done');

    Route::post('/tasks/{task}/undone', [TaskController::class, 'undone'])
        ->middleware('auth')
        ->name('task.undone');

    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->middleware('auth')
        ->name('task.update');

    Route::delete('/tasks/{task}', [TaskController::class, 'delete'])
        ->middleware('auth')
        ->name('task.delete');

});


