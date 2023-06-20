<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\{Auth, Validator};
use \Carbon\Carbon;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            $credenciales = $request->only('name', 'password');

            if (!Auth::attempt($credenciales)) {
                $status  = Response::HTTP_UNPROCESSABLE_ENTITY;
                $message = "Credenciales Inválidas.";

                return response()->json(['status' => $status, 'message' => $message]);
            }

            $usuario = Auth::user();
            $tokenResult = $usuario->createToken("API TOKEN")->plainTextToken;

            $data   = [
                'usuario'  => $usuario,
                'token'    => $tokenResult,
            ];

            return response()->json(['data' => $data]);
        } catch (\Throwable $ex) {

            $status  = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $ex->getMessage(); //"Ocurrió un error al iniciar la sesión de usuario.";

            return response()->json(['status' => $status, 'message' => $message]);
        }
    }


    /*     public function logout() { 

        Auth::user()->token()->revoke();

        $data    = null;
        $status  = Response::HTTP_OK;        
        $message = "Sesión finalizada correctamente.";

        //return response_success($data, $status, $message);
        
    } */
}
