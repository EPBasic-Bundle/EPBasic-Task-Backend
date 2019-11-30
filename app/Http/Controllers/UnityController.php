<?php

namespace App\Http\Controllers;

use App\Subject;
use App\Unity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class UnityController extends Controller
{
    // Unidades por asignatura
    public function index(Request $request, $subject_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subject = Subject::where('user_id', $user->sub)->where('id', $subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $units = Unity::where('subject_id', $subject_id)->orderBy('number', 'desc')->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'units' => $units,
            ]);
        }
    }

    // Unidad por ID
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $unity = Unity::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            if ($json == true) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'unity' => $unity,
                ]);
            } else {
                return $unity;
            }
        }
    }

    // Añadir unidad
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
                'number' => 'required',
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
                    $unity = new Unity();

                    $unity->subject_id = $params->subject_id;
                    $unity->evaluation_id = $params->evaluation_id;
                    $unity->number = $params->number;

                    $unity->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'unity' => $this->detail($request, $unity->id, false),
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
                'subject_id' => 'required',
                'number' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                );
            } else {
                $user = app('App\Http\Controllers\UserController')
                    ->getAuth($request->header('Authorization'));

                $unity = Unity::find($id);

                if ($unity && is_object($unity)) {
                    $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

                    if ($subject && is_object($subject)) {
                        $unity->evaluation_id = $params->evaluation_id;
                        $unity->number = $params->number;

                        $unity->update();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'unity' => $this->detail($request, $unity->id, false),
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

    //Eliminar unidad
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $unity = Unity::find($id);

        if ($unity && is_object($unity)) {
            $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

            if ($subject && is_object($subject)) {
                $unity->load('tasks');
                $unity->load('exams');

                foreach ($unity->tasks as $task) {
                    $task->delete();
                }

                foreach ($unity->exams as $exam) {
                    $exam->delete();
                }

                $unity->delete();

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
