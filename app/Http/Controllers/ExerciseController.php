<?php

namespace App\Http\Controllers;

use App\Exercise;
use App\Subject;
use App\Page;
use App\Task;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExerciseController extends Controller
{
    // Marcar como hecho
    public function markAsDone(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $exercise = Exercise::find($id);
        $page = Page::find($exercise->page_id);
        $task = Task::find($page->task_id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->first();

        if ($subject) {
            $exercise->done = 1;
            $exercise->update();
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'exercise' => $exercise,
        ]);
    }

    // Marcar como hecho la tarea entera
    public function markAsDoneAll(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $task = Task::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->first();

        if ($subject) {
            $task->done = 1;
            $task->update();

            $task->load('pages');

            if ($task['pages']) {
                foreach($task['pages'] as $page) {
                    $page->load('exercises');

                    foreach($page['exercises'] as $exercise) {
                        $exercise->done = 1;
                        $exercise->update();
                    }
                }
            }
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'task' => $task,
        ]);
    }
}
