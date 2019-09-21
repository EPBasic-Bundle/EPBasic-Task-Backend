<?php

namespace App\Http\Middleware;

use Closure;
use JwtAuth;

class ApiAuthMiddleware
{

    public function handle($request, Closure $next)
    {
        //Comprobar si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            return $next($request);
        }else{
            $data = array(
                'code' => 200,
                'status' => 'error',
                'message' => 'User not connected'
            );

            return response()->json($data, $data['code']);
        }
    }
}
