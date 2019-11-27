<?php

namespace App\Http\Controllers;

use App\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class YearController extends Controller
{
    // Años
    public function index(Request $request, $study_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $years = Year::where('study_id', $study_id)->where('user_id', $user->sub)->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'years' => $years,
        ]);
    }

    // Añadir año
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
                $year = new Year();

                $year->user_id = $user->sub;
                $year->study_id = $params->study_id;
                $year->start = $params->start;
                $year->end = $params->end;

                $year->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'year' => $year,
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

    // Actualizar año
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

                $year = Year::where('id', $id)->where('user_id', $user->sub)->first();

                if ($year && is_object($year)) {

                    $year->start = $params->start;
                    $year->end = $params->end;

                    $year->update();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'study' => $year,
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

    //Eliminar año
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $year = Year::where('id', $id)->where('user_id', $user->sub)->first();

        if ($year && is_object($year)) {

            $year->delete();

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
