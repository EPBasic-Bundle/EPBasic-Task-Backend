<?php

namespace App\Http\Controllers;

use App\Event;
use App\Exercise;
use App\Page;
use App\Subject;
use App\Task;
use App\Unity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class TaskController extends Controller
{
    // Tareas
    public function index(Request $request, $unity_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $unity = Unity::find($unity_id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $tasks = Task::where('unity_id', $unity->id)->get()->load('pages');

            foreach ($tasks as $task) {
                foreach ($task['pages'] as $page) {
                    $page->load('exercises');
                }
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'tasks' => $tasks,
            ]);
        }
    }

    // Tareas
    public function indexToDo(Request $request, $subject_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subject = Subject::where('user_id', $user->sub)->where('id', $subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $tasks = Task::where('subject_id', $subject->id)->where('done', 0)->get()->load('pages');

            foreach ($tasks as $task) {
                foreach ($task['pages'] as $page) {
                    $page->load('exercises');
                }
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'tasks' => $tasks,
            ]);
        }
    }

    // Tarea por ID
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $task = Task::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $task->load('pages');
            $task->load('book');

            foreach ($task['pages'] as $page) {
                $page->load('exercises');
            }

            if ($json == true) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'task' => $task,
                ]);
            } else {
                return $task;
            }
        }
    }

    // Marcar tarea como hecha
    public function changeStatus(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $task = Task::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            if ($task->done == 1) {
                $task->done = 0;
            } else {
                $task->done = 1;

                $task->load('pages');

                if ($task['pages']) {
                    foreach ($task['pages'] as $page) {
                        $page->load('exercises');

                        foreach ($page['exercises'] as $exercise) {
                            $exercise->done = 1;
                            $exercise->update();
                        }
                    }
                }
            }

            $task->update();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'task' => $task,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    // Cambiar día de entrega
    public function updateDeliveryDay(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $task = Task::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $task->delivery_date = date_format(date_create(json_decode($request->input('json', null))), 'Y-m-d H:i:s');

            $task->update();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'task' => $task,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    // Añadir tareas
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, [
                'subject_id' => 'required',
                'unity_id' => 'required',
                'title' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $subject = Subject::where('user_id', $user->sub)->where('id', $params->subject_id)->where('year_id', $user->year_id)->first();

                if ($subject && is_object($subject)) {

                    $task = new Task();

                    $task->subject_id = $params->subject_id;
                    $task->book_id = $params->book_id;
                    $task->unity_id = $params->unity_id;
                    $task->title = $params->title;
                    $task->description = $params->description;
                    $task->mark = $params->mark;
                    $task->delivery_date = $params->delivery_date;
                    $task->done = false;
                    $task->save();

                    if (isset($params->pages)) {
                        foreach ($params->pages as $Page) {
                            $page = new Page();

                            $page->task_id = $task->id;
                            $page->number = $Page->number;
                            $page->save();

                            foreach ($Page->exercises as $Exercise) {
                                $exercise = new Exercise();

                                $exercise->page_id = $page->id;
                                $exercise->number = $Exercise->number;
                                $exercise->done = false;
                                $exercise->save();
                            }
                        }
                    }

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'task' => $this->detail($request, $task->id, false),
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 200,
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    // Actualizar unidad
    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'title' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                );
            } else {
                $user = app('App\Http\Controllers\UserController')
                    ->getAuth($request->header('Authorization'));

                $task = Task::find($id);

                if ($task && is_object($task)) {
                    $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->where('year_id', $user->year_id)->first();

                    if ($subject && is_object($subject)) {
                        $task->book_id = $params->book_id;
                        $task->title = $params->title;
                        $task->description = $params->description;
                        $task->mark = $params->mark;
                        $task->delivery_date = $params->delivery_date;

                        $task->update();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'task' => $this->detail($request, $task->id, false),
                        );
                    } else {
                        $data = array(
                            'status' => 'error',
                            'code' => 200,
                        );
                    }
                }
            }

            return response()->json($data, $data['code']);
        }
    }

    //Eliminar tarea
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $task = Task::find($id);

        if ($task && is_object($task)) {
            $subject = Subject::where('user_id', $user->sub)->where('id', $task->subject_id)->where('year_id', $user->year_id)->first();

            if ($subject && is_object($subject)) {
                $task->load('pages');

                foreach ($task->pages as $page) {
                    foreach ($page->exercises as $exercise) {
                        $exercise->delete();
                    }
                    $page->delete();
                }

                $event = Event::where('user_id', $user->sub)->where('task_id', $task->id)->first();

                if ($event && is_object($event)) {
                    $event->delete();
                }

                $task->delete();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                );
            } else {
                $data = [
                    'code' => 200,
                    'status' => 'error',
                ];
            }
        } else {
            $data = [
                'code' => 200,
                'status' => 'error',
            ];
        }
        return response()->json($data, $data['code']);
    }
}
