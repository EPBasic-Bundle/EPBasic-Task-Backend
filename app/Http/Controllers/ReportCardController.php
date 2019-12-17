<?php
namespace App\Http\Controllers;

use App\Mark;
use App\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class ReportCardController extends Controller
{
    // Boletines
    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $report_cards = ReportCard::where('year_id', $user->year_id)->where('user_id', $user->sub)->get();

        foreach ($report_cards as $report_card) {
            $report_card->load('marks');
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'report_cards' => $report_cards,
        ]);
    }

    //Crear boletín
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

            $validate = Validator::make($params_array, [
                'evaluation_id' => 'required',
                'type' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $report_card = ReportCard::where('user_id', $user->sub)->where('evaluation_id', $params->evaluation_id)->where('type', $params->type)->first();

                if ($report_card && is_object($report_card)) {
                    $report_card->load('marks');

                    foreach ($report_card['marks'] as $report_card_mark) {
                        $report_card_mark->delete();
                    }

                    $report_card->delete();
                }

                $report_card = new ReportCard();

                $report_card->user_id = $user->sub;
                $report_card->evaluation_id = $params->evaluation_id;
                $report_card->year_id = $user->year_id;
                $report_card->type = $params->type;

                $report_card->save();

                foreach ($params->marks as $mark) {
                    $newMark = new Mark();

                    $newMark->report_card_id = $report_card->id;
                    $newMark->subject_id = $mark->subject_id;
                    $newMark->mark_wd = $mark->mark_wd;
                    $newMark->mark = $mark->mark;

                    $newMark->save();
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

    //Eliminar boletín
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $report_card = ReportCard::where('user_id', $user->sub)->where('id', $id)->first();

        if ($report_card && is_object($report_card)) {
            $report_card->load('marks');

            foreach ($report_card['subjects'] as $report_card) {
                $report_card->delete();
            }

            $report_card->delete();

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
