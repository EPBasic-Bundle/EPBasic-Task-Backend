<?php
namespace App\Http\Controllers;

use App\User;
use App\Subject;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class SubjectController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth');
    }

    // Asignaturas
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $subjects = Subject::where('user_id', $user->sub)->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'subjects' => $subjects,
        ]);
    }

    // Asignatura
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $subject = Subject::where('id', $id)->where('user_id', $user->sub)->first();

        if ($json == true) {
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'subject' => $subject,
            ]);
        } else {
            return $subject;
        }
    }

    // Añadir asignatura
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
                'primary_color' => 'required',
                'secondary_color' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $subject = new Subject();

                $subject->user_id = $user->sub;
                $subject->name = $params->name;
                $subject->primary_color = $params->primary_color;
                $subject->secondary_color = $params->secondary_color;
                $subject->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'subject' => $this->detail($request, $subject->id, false)
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

    // Actualizar asignatura
    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, ['name' => 'required']);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                );
            } else {
                $user = app('App\Http\Controllers\UserController')
                    ->getAuth($request->header('Authorization'));

                $subject = Subject::where('id', $id)->where('user_id', $user->sub)->first();

                if ($subject && is_object($subject)) {
                    $subject->name = $params->name;
                    $subject->primary_color = $params->primary_color;
                    $subject->secondary_color = $params->secondary_color;

                    $subject->update();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'subject' => $this->detail($request, $subject->id, false)
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

    //Eliminar asignatura
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $subject = Subject::where('user_id', $user->sub)->where('id', $id)->first();

        if ($subject && is_object($subject)) {
            $subject->load('books');
            $subject->load('units');

            foreach ($subject->books as $book) {
                $book->delete();
            }

            foreach ($subject->units as $unity) {
                $unity->delete();
            }

            $subject->delete();

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
