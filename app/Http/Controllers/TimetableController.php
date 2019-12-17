<?php
namespace App\Http\Controllers;

use App\Timetable;
use App\TimetableHour;
use App\TimetableSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class TimetableController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    //Horario
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $timetable = Timetable::where('user_id', $user->sub)->where('year_id', $user->year_id)->first();

        if ($timetable && is_object($timetable)) {
            $timetable->load('subjects')->load('hours');

            $subjects = array_chunk($timetable->subjects->toArray(), 5);
            $timetable->subjects = null;

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'timetable' => $timetable,
                'subjects' => $subjects,
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'status' => 'error',
            ]);
        }
    }

    /**********************************************************************************************/
    /* CRUD */
    /**********************************************************************************************/

    //Crear horario
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, ['rows' => 'required']);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $timetable = Timetable::where('user_id', $user->sub)->where('year_id', $user->year_id)->first();

                if ($timetable && is_object($timetable)) {
                    $timetable->load('subjects');

                    foreach ($timetable['subjects'] as $timetableSubject) {
                        $timetableSubject->delete();
                    }

                    $timetable->load('hours');

                    foreach ($timetable['hours'] as $timetableHour) {
                        $timetableHour->delete();
                    }

                    $timetable->delete();
                }

                $timetable = new Timetable();

                $timetable->user_id = $user->sub;
                $timetable->rows = $params->rows;
                $timetable->year_id = $user->year_id;

                $timetable->save();

                foreach ($params->subjects as $subjectRow) {
                    foreach ($subjectRow as $subject) {
                        $timetableSubject = new TimetableSubject();

                        $timetableSubject->timetable_id = $timetable->id;
                        $timetableSubject->subject_id = $subject->subject_id;
                        $timetableSubject->save();
                    }
                }

                foreach ($params->hours as $hour) {
                    $timetableHour = new TimetableHour();

                    $timetableHour->timetable_id = $timetable->id;
                    $timetableHour->hour_start = $hour->hour_start;
                    $timetableHour->hour_end = $hour->hour_end;
                    $timetableHour->save();
                }

                $data = array(
                    'status' => 'success',
                    'code' => 200,
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

    //Eliminar timetable
    public function destroy(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $timetable = Timetable::where('user_id', $user->sub)->where('year_id', $user->year_id)->first();

        if ($timetable && is_object($timetable)) {
            $timetable->load('subjects')->load('hours');

            foreach ($timetable['subjects'] as $subject) {
                $subject->delete();
            }

            foreach ($timetable['hours'] as $hour) {
                $hour->delete();
            }

            $timetable->delete();

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
