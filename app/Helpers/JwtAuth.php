<?php
namespace App\Helpers;

use App\User;
use Firebase\JWT\JWT;

class JwtAuth
{
    public $key;

    public function __construct()
    {
        $this->key = '8uAbV0Jpv33aNzPrpQml5ujlc6TmJqH3';
    }

    public function signup($email, $password, $getToken = false)
    {
        //Buscar si existe el usuario con sus credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password,
        ])->first();

        //Comprobar si son correctas (objeto)
        if (is_object($user)) {
            //Generar el token con los datos del usuario
            $token = array(
                'sub' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'year_id' => $user->year_id,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60),
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

            //Devolver identidad o token
            if ($getToken == true) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
        } else {
            $data = null;
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        } else {
            return $auth;
        }
    }

    public function returnToken($user, $getToken = false)
    {
        $token = array(
            'sub' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'year_id' => $user->year_id,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60),
        );

        $jwt = JWT::encode($token, $this->key, 'HS256');
        $decoded = JWT::decode($jwt, $this->key, array('HS256'));

        //Devolver identidad o token
        if ($getToken == true) {
            $data = $jwt;
        } else {
            $data = $decoded;
        }

        return $data;
    }

}
