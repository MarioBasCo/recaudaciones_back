<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function enviarCorreo(Request $request)
    {
        // Lógica para obtener los datos del cliente y el contenido del correo

        // Envía el correo
        $content = '';
        Mail::to($request->email)->send(new NotificacionMail(''));

        return response()->json(['message' => 'Correo enviado'], 200);
    }
}
