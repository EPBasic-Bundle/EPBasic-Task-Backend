<?php

namespace App\Http\Controllers;

use App\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class StudyController extends Controller
{
    // Estudios
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $studies = Study::where('user_id', $user->sub)->get();

        if ($studies && is_object($studies)) {
            foreach ($studies as $study) {
                $study->load('years');

                foreach ($study['years'] as $year) {
                    $year->load('evaluations');

                }
            }
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'studies' => $studies,
        ]);
    }

    // Añadir estudios
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, [
                'name' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $study = Study::where('name', $params->name)->where('user_id', $user->sub)->first();

                if (!$study && !is_object($study)) {
                    $study = new Study();

                    $study->user_id = $user->sub;
                    $study->name = $params->name;

                    $study->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'study' => $study,
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

    // Actualizar estudios
    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'name' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                );
            } else {
                $user = app('App\Http\Controllers\UserController')
                    ->getAuth($request->header('Authorization'));

                $study = Study::find($id);

                if ($study && is_object($study)) {

                    $study->name = $params->name;

                    $study->update();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'study' => $study,
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 200,
                    );
                }
            }

            return response()->json($data, $data['code']);
        }
    }

    //Eliminar estudio
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $study = Study::find($id);

        if ($study && is_object($study)) {

            $study->delete();

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

        return response()->json($data, $data['code']);
    }
}
