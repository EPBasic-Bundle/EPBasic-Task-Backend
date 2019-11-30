<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['register', 'login', 'unblockUser']]);
    }

    /**********************************************************************************************/
    /* Funciones */
    /**********************************************************************************************/

    public function getAuth($token)
    {
        $jwtAuth = new JwtAuth();
        return $jwtAuth->checkToken($token, true);
    }

    //Comprobar que el ID de usuario coincida con el Token
    public function checkRealIdentity(Request $request, $user_id)
    {
        $user = $this->getAuth($request->header('Authorization'));

        if ($user->sub == $user_id) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'real' => true,
            );
        } else {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'real' => false,
            );
        }

        return response()->json($data, $data['code']);
    }

    /**********************************************************************************************/
    /* CRUD */
    /**********************************************************************************************/

    //Registrar
    public function register(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array) && !empty($params)) {
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'msCode' => 1,
                    'code' => 200,
                    'errors' => $validate->errors(),
                );
            } else {
                $params_array = array_map('trim', $params_array); //Limpiar datos

                $pwd = hash('sha256', $params->password);

                $user = new User();
                $user->name = $params->name;
                $user->surname = $params->surname;
                $user->email = $params->email;
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'user' => $user,
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
                'json' => $json,
            );
        }

        return response()->json($data, $data['code']);
    }

    //Iniciar sesión
    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth();

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 200,
                'errors' => $validate->errors(),
            );
        } else {
            //Cifrar contraseña
            $pwd = hash('sha256', $params->password);

            $identity = $jwtAuth->signup($params->email, $pwd);
            $token = $jwtAuth->signup($params->email, $pwd, true);

            if (!is_null($identity) && !is_null($token)) {
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'identity' => $identity,
                    'token' => $token,
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'msCode' => 1,
                    'code' => 200,
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    public function unblockUser(Request $request, $user_id, $pinCode)
    {
        $user = User::where('id', $user_id)->where('pinCode', $pinCode)->first();

        $jwtAuth = new JwtAuth();

        $identity = $jwtAuth->signup($user->email, $user->password);
        $token = $jwtAuth->signup($user->email, $user->password, true);

        if (!is_null($identity) && !is_null($token)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'identity' => $identity,
                'token' => $token,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    public function changePinCode(Request $request)
    {
        $user = $this->getAuth($request->header('Authorization'));

        if (!is_null($user)) {
            $pinCode = rand(100000, 999999);

            $user = User::find($user->sub);
            $user->pinCode = $pinCode;
            $user->update();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'pinCode' => $pinCode,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getPinCode(Request $request)
    {
        $user = $this->getAuth($request->header('Authorization'));

        if (!is_null($user)) {
            $user = User::find($user->sub);

            $data = array(
                'status' => 'success',
                'code' => 200,
                'pinCode' => $user->pinCode,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    public function changeYear(Request $request, $year_id)
    {
        $jwtAuth = new JwtAuth();

        $user = $this->getAuth($request->header('Authorization'));

        if (!is_null($user)) {
            $user = User::find($user->sub);
            $user->year_id = $year_id;
            $user->update();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'identity' => $jwtAuth->returnToken($user),
                'token' => $jwtAuth->returnToken($user, 1),
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }

    //Actualizar usuario
    public function update(Request $request)
    {
        $jwtAuth = new JwtAuth;
        $token = $request->header('Authorization');

        //Comprobar si el usuario está identificado
        $checkToken = $this->getAuth($token);

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($json)) {
            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha_dash',
                'email' => 'required|email|unique:users,' . $user->sub,
            ]);

            $user = User::find($user->sub);

            $user->name = $params->name;
            $user->surname = $params->surname;
            $user->email = $params->email;
            $user->dark_mode = $params->dark_mode;

            $user->update();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'changes' => $params_array,
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 200,
            );
        }

        return response()->json($data, $data['code']);
    }
}
