<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

use App\Book;
use App\Subject;

class BookController extends Controller
{
    // Libros por asignatura
    public function index(Request $request, $subject_id)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $subject = Subject::where('user_id', $user->sub)->where('id', $subject_id)->first();

        if ($subject) {
            $books = Book::where('subject_id', $subject_id)->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'books' => $books,
            ]);
        }
    }

    // Libro por ID
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $book = Book::where('id', $id)->first();

        $subject = Subject::where('user_id', $user->sub)->where('id', $book->subject_id)->first();

        if ($subject) {
            if ($json == true) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'book' => $book,
                ]);
            } else {
                return $book;
            }
        }
    }

    // Añadir libro
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
                'name' => 'required',
                'pages_quantity' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $subject = Subject::where('user_id', $user->sub)->where('id', $params->subject_id)->first();

                if ($subject && is_object($subject)) {
                    $book = new Book();

                    $book->name = $params->name;
                    $book->subject_id = $params->subject_id;
                    $book->pages_quantity = $params->pages_quantity;
                    $book->image = $params->image;
                    $book->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'book' => $this->detail($request, $book->id, false)
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

    // Actualizar libro
    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'name' => 'required',
                'subject_id' => 'required',
                'pages_quantity' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 200,
                );
            } else {
                $user = app('App\Http\Controllers\UserController')
                    ->getAuth($request->header('Authorization'));

                $book = Book::find($id);

                if ($book && is_object($book)) {
                    $subject = Subject::where('user_id', $user->sub)->where('id', $book->subject_id)->first();

                    if ($subject && is_object($subject)) {
                        $book->name = $params->name;
                        $book->subject_id = $params->subject_id;
                        $book->pages_quantity = $params->pages_quantity;
                        $book->image = $params->image;

                        $book->update();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'book' => $this->detail($request, $book->id, false)
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

    //Eliminar libro
    public function destroy(Request $request, $id)
    {
        $user = app('App\Http\Controllers\UserController')
                ->getAuth($request->header('Authorization'));

        $book = Book::find($id);

        if ($book && is_object($book)) {
            $subject = Subject::where('user_id', $user->sub)->where('id', $book->subject_id)->first();

            if ($subject && is_object($subject)) {
                $book->delete();

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
