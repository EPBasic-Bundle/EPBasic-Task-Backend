<?php
namespace App\Http\Controllers;

use App\Exam;
use App\Subject;
use App\Task;
use App\Unity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    // Asignaturas
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subjects = Subject::where('user_id', $user->sub)->where('year_id', $user->year_id)->get();

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

        $subject = Subject::where('id', $id)->where('user_id', $user->sub)->where('year_id', $user->year_id)->first();

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

    // Asignatura con toDo
    public function indexWithAllToDo(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subjects = Subject::where('user_id', $user->sub)->where('year_id', $user->year_id)->get();

        if ($subjects && is_object($subjects)) {
            foreach ($subjects as $subject) {
                $subject->tasks = Task::where('subject_id', $subject->id)->where('done', 0)->get();
                $subject->exams = Exam::where('subject_id', $subject->id)->where('done', 0)->get();
            }

            $data = array(
                'code' => 200,
                'status' => 'success',
                'subjects' => $subjects,
            );
        } else {
            $data = array(
                'code' => 200,
                'status' => 'error',
            );
        }

        return response()->json($data, $data['code']);
    }

    // Asignatura con TODO
    public function detailWithAll(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subject = Subject::where('id', $id)->where('user_id', $user->sub)->where('year_id', $user->year_id)->first();

        if ($subject && is_object($subject)) {
            foreach ($subject->units as $unity) {
                $subject->tasks = Task::where('unity_id', $unity->id)->get();
                $subject->exams = Task::where('unity_id', $unity->id)->get();
            }

            $data = array(
                'code' => 200,
                'status' => 'success',
                'subject' => $subject,
            );
        } else {
            $data = array(
                'code' => 200,
                'status' => 'error',
            );
        }

        return response()->json($data, $data['code']);
    }

    // Seleccionar unidad actual
    public function setCurrentUnity(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $unity = Unity::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $subject->current_unity = $id;
            $subject->save();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'subject' => $subject,
            ]);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
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
                $subject->tasks_percentage = $params->tasks_percentage;
                $subject->exams_percentage = $params->exams_percentage;
                $subject->projects_percentage = $params->projects_percentage;
                $subject->behaviour_percentage = $params->behaviour_percentage;
                $subject->tasks_has_mark = $params->tasks_has_mark;
                $subject->year_id = $user->year_id;

                $subject->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'subject' => $this->detail($request, $subject->id, false),
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

                $subject = Subject::where('id', $id)->where('user_id', $user->sub)->where('year_id', $user->year_id)->first();

                if ($subject && is_object($subject)) {
                    $subject->name = $params->name;
                    $subject->primary_color = $params->primary_color;
                    $subject->secondary_color = $params->secondary_color;
                    $subject->tasks_percentage = $params->tasks_percentage;
                    $subject->exams_percentage = $params->exams_percentage;
                    $subject->projects_percentage = $params->projects_percentage;
                    $subject->behavior_percentage = $params->behavior_percentage;
                    $subject->tasks_has_mark = $params->tasks_has_mark;

                    $subject->update();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'subject' => $this->detail($request, $subject->id, false),
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

        $subject = Subject::where('user_id', $user->sub)->where('id', $id)->where('year_id', $user->year_id)->first();

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
