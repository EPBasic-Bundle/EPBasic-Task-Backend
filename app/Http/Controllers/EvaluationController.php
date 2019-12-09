<?php

namespace App\Http\Controllers;

use App\Evaluation;
use App\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class EvaluationController extends Controller
{
    // Evaluaciones
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $evaluations = Evaluation::where('year_id', $user->year_id)->where('user_id', $user->sub)->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'evaluations' => $evaluations,
        ]);
    }

    // Evaluaciones con notas
    public function indexMarks(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $evaluations = Evaluation::where('year_id', $user->year_id)->where('user_id', $user->sub)->get();

        foreach ($evaluations as $evaluation) {
            $evaluation->marks = Mark::where('evaluation_id', $evaluation->id)->get();
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'evaluations' => $evaluations,
        ]);
    }

    // Añadir evaluación
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, [
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
                $evaluation = new Evaluation();

                $evaluation->user_id = $user->sub;
                $evaluation->start = $params->start;
                $evaluation->end = $params->end;
                $evaluation->year_id = $params->year_id;

                $evaluation->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'evaluation' => $evaluation,
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

    // Actualizar evaluación
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

                $evaluation = Evaluation::where('id', $id)->where('user_id', $user->sub)->first();

                if ($evaluation && is_object($evaluation)) {

                    $evaluation->start = $params->start;
                    $evaluation->end = $params->end;

                    $evaluation->update();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'evaluation' => $evaluation,
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

    //Eliminar evaluación
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $evaluation = Evaluation::where('id', $id)->where('user_id', $user->sub)->first();

        if ($evaluation && is_object($evaluation)) {

            $evaluation->delete();

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
