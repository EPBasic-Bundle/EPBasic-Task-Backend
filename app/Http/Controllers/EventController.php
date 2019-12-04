<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class EventController extends Controller
{
    // Eventos
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $events = Event::where('user_id', $user->sub)->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'events' => $events,
        ]);
    }

    // Eventos
    public function indexNotPassed(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $events = Event::where('user_id', $user->sub)->whereNotNull('exam_id')->where('start', '>=', date('Y-m-d'))->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'events' => $events,
        ]);
    }

    // Evento por ID de tarea
    public function taskEvent(Request $request, $task_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $event = Event::where('user_id', $user->sub)->where('task_id', $task_id)->first();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'event' => $event,
        ]);
    }

    // Evento por ID de examen
    public function examEvent(Request $request, $exam_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $event = Event::where('user_id', $user->sub)->where('exam_id', $exam_id)->first();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'event' => $event,
        ]);
    }

    // Evento por ID de proyecto
    public function projectEvent(Request $request, $project_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $event = Event::where('user_id', $user->sub)->where('project_id', $project_id)->first();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'event' => $event,
        ]);
    }

    // Añadir evento
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, [
                'title' => 'required',
                'primary_color' => 'required',
                'secondary_color' => 'required',
                'start' => 'required',
                'end' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $event = new Event();

                $event->user_id = $user->sub;
                $event->start = $params->start;
                $event->end = $params->end;
                $event->title = $params->title;
                $event->primary_color = $params->primary_color;
                $event->secondary_color = $params->secondary_color;
                $event->task_id = $params->task_id;
                $event->exam_id = $params->exam_id;
                $event->project_id = $params->project_id;

                $event->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'event' => $event,
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    // Actualizar evento
    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, [
                'title' => 'required',
                'primary_color' => 'required',
                'secondary_color' => 'required',
                'start' => 'required',
                'end' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $event = Event::where('id', $id)->where('user_id', $user->sub)->first();

                if ($event && is_object($event)) {
                    $event->start = $params->start;
                    $event->end = $params->end;
                    $event->title = $params->title;
                    $event->primary_color = $params->primary_color;
                    $event->secondary_color = $params->secondary_color;
                    $event->task_id = $params->task_id;
                    $event->exam_id = $params->exam_id;

                    $event->update();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'event' => $event,
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

    // Eliminar evento
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $event = Event::where('id', $id)->where('user_id', $user->sub)->first();

        if ($event && is_object($event)) {
            $event->delete();

            $data = array(
                'status' => 'success',
                'code' => 200,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

}
