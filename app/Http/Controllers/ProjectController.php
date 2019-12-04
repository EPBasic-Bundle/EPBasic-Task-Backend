<?php

namespace App\Http\Controllers;

use App\Event;
use App\Project;
use App\Subject;
use App\Unity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class ProjectController extends Controller
{
    // Proyectos
    public function index(Request $request, $unity_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $unity = Unity::find($unity_id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $projects = Project::where('unity_id', $unity->id)->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'projects' => $projects,
            ]);
        }
    }

    // Proyectos TODO
    public function indexToDo(Request $request, $subject_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subject = Subject::where('user_id', $user->sub)->where('id', $subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $projects = Project::where('subject_id', $subject->id)->where('done', 0)->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'projects' => $projects,
            ]);
        }
    }

    // Proyecto por ID
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $project = Project::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $project->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            if ($json == true) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'project' => $project,
                ]);
            } else {
                return $project;
            }
        }
    }

    // Marcar proyecto como hecho
    public function changeStatus(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $project = Project::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $project->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            if ($project->done == 1) {
                $project->done = 0;
            } else {
                $project->done = 1;
            }

            $project->update();
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'project' => $project,
        ]);
    }

    // Añadir proyecto
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
                    $project = new Project();

                    $project->subject_id = $params->subject_id;
                    $project->unity_id = $params->unity_id;
                    $project->title = $params->title;
                    $project->description = $params->description;
                    $project->mark = $params->mark;
                    $project->delivery_date = $params->delivery_date;
                    $project->done = 0;

                    if ($params->mark !== null) {
                        $project->done = 1;
                    }

                    $project->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'project' => $this->detail($request, $project->id, false),
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

    // Actualizar proyecto
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

                $project = Project::find($id);

                if ($project && is_object($project)) {
                    $subject = Subject::where('user_id', $user->sub)->where('id', $project->subject_id)->where('year_id', $user->year_id)->first();

                    if ($subject && is_object($subject)) {
                        $project->title = $params->title;
                        $project->description = $params->description;
                        $project->mark = $params->mark;
                        $project->delivery_date = $params->delivery_date;

                        if ($params->mark !== null) {
                            $project->done = 1;
                        }

                        $project->update();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'project' => $this->detail($request, $project->id, false),
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

    //Eliminar proyecto
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $project = Project::find($id);

        if ($project && is_object($project)) {
            $subject = Subject::where('user_id', $user->sub)->where('id', $project->subject_id)->where('year_id', $user->year_id)->first();

            if ($subject && is_object($subject)) {
                $event = Event::where('user_id', $user->sub)->where('project_id', $project->id)->first();

                if ($event && is_object($event)) {
                    $event->delete();
                }

                $project->delete();

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
