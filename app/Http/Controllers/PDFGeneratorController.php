<?php

namespace App\Http\Controllers;

use App\Study;
use Illuminate\Http\Request;

class PDFGeneratorController extends Controller
{

    public function index(Request $request)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $studies = Study::where('user_id', $user->sub)->get();

        $date = date("Y-m-d");

        $view = \View::make('invoice', compact('user', 'studies', 'date'))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view);

        return $pdf->stream('invoice.pdf');
    }
}
