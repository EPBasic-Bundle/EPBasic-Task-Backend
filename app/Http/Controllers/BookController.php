<?php
namespace App\Http\Controllers;

use App\Book;
use App\Subject;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Storage;
use Validator;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['getBookPDF']]);
    }

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
        } else {
            return response()->json([
                'status' => 'error',
                'code' => 200,
            ]);
        }
    }

    // Libro por ID
    public function detail(Request $request, $id, $json = true)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $book = Book::find($id);

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
        } else {
            return response()->json([
                'status' => 'error',
                'code' => 200,
            ]);
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
                    $book->pdf_name = $params->pdf_name;
                    $book->last_seen_page = 1;

                    $book->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'book' => $this->detail($request, $book->id, false),
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
                'pages_quantity' => 'required',
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
                        $book->pdf_name = $params->pdf_name;

                        $book->update();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'book' => $this->detail($request, $book->id, false),
                        );
                    } else {
                        $data = array(
                            'status' => 'error',
                            'code' => 200,
                        );
                    }
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

    //Subir pdf
    public function pdfUpload(Request $request, $book_id)
    {
        $pdf = $request->file('file');

        $validate = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpg,JPG,jpeg,png,gif',
        ]);

        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        if (!$pdf || $validate->fails()) {
            $data = array(
                'code' => 200,
                'status' => 'error',
            );
        } else {
            $pdf_name = time() . $pdf->getClientOriginalName();

            Storage::disk('books')->put($pdf_name, File::get($pdf));
            $isset = Storage::disk('books')->exists($pdf_name);
            $data = array(
                'code' => 200,
                'status' => 'success',
                'pdf' => $pdf_name,
                'iss' => $isset
            );
        }

        return response()->json($data, $data['code']);
    }

    // Cambiar última hoja
    public function lastSeenPage(Request $request, $book_id, $page_number)
    {
        $user = app('App\Http\Controllers\UserController')
            ->getAuth($request->header('Authorization'));

        $book = Book::find($book_id);

        $subject = Subject::where('user_id', $user->sub)->where('id', $book->subject_id)->first();

        if ($subject) {
            $book->last_seen_page = $page_number;
            $book->update();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'book' => $book
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    //PDF libro
    public function getBookPDF($filename = null)
    {
        $isset = Storage::disk('books')->exists($filename);

        $file = Storage::disk('books')->get($filename);

        return Response::make($file, 200);
    }
}
