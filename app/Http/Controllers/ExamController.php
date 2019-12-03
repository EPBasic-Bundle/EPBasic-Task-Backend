<?php

namespace App\Http\Controllers;

use App\Event;
use App\Exam;
use App\Subject;
use App\Unity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class ExamController extends Controller
{
    // Examen
    public function index(Request $request, $unity_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $unity = Unity::find($unity_id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $unity->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $exams = Exam::where('unity_id', $unity->id)->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'exams' => $exams,
            ]);
        }
    }

    // Examen por asignatura
    public function indexToDo(Request $request, $subject_id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $subject = Subject::where('user_id', $user->sub)->where('id', $subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $exams = Exam::where('subject_id', $subject->id)->where('done', false)->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'exams' => $exams,
            ]);
        }
    }

    // Examen por ID
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $exam = Exam::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $exam->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            if ($json == true) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'exam' => $exam,
                ]);
            } else {
                return $exam;
            }
        }
    }

    // Marcar tarea como hecha
    public function changeStatus(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $exam = Exam::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $exam->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            if ($exam->done == 1) {
                $exam->done = 0;
            } else {
                $exam->done = 1;
            }

            $exam->update();
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'exam' => $exam,
        ]);
    }

    // Cambiar día del examen
    public function updateExamDay(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $exam = Exam::find($id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $exam->subject_id)->where('year_id', $user->year_id)->first();

        if ($subject) {
            $exam->exam_date = date_format(date_create(json_decode($request->input('json', null))), 'Y-m-d H:i:s');

            $exam->update();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'exam' => $exam,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    // Añadir examen
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
                //'delivery_date' => 'required',
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
                    $exam = new Exam();

                    $exam->subject_id = $params->subject_id;
                    $exam->unity_id = $params->unity_id;
                    $exam->title = $params->title;
                    $exam->description = $params->description;
                    $exam->mark = $params->mark;
                    $exam->exam_date = $params->exam_date;

                    if ($params->mark !== null) {
                        $exam->done = 1;
                    }

                    $exam->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'exam' => $this->detail($request, $exam->id, false),
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

    // Actualizar examen
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

                $exam = Exam::find($id);

                if ($exam && is_object($exam)) {
                    $subject = Subject::where('user_id', $user->sub)->where('id', $exam->subject_id)->where('year_id', $user->year_id)->first();

                    if ($subject && is_object($subject)) {
                        $exam->title = $params->title;
                        $exam->description = $params->description;
                        $exam->mark = $params->mark;
                        $exam->exam_date = $params->exam_date;

                        if ($params->mark !== null) {
                            $exam->done = 1;
                        }

                        $exam->update();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'exam' => $this->detail($request, $exam->id, false),
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

    //Eliminar examen
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $exam = Exam::find($id);

        if ($exam && is_object($exam)) {
            $subject = Subject::where('user_id', $user->sub)->where('id', $exam->subject_id)->where('year_id', $user->year_id)->first();

            if ($subject && is_object($subject)) {
                $event = Event::where('user_id', $user->sub)->where('exam_id', $exam->id)->first();

                if ($event && is_object($event)) {
                    $event->delete();
                }

                $exam->delete();

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
