<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionMail extends Mailable
{
    use Queueable, SerializesModels;
    public $contenidoCorreo;

    public function __construct($contenidoCorreo)
    {
        $this->contenidoCorreo = $contenidoCorreo;
    }

    public function build()
    {
        return $this->subject('Notificación de pago')
                    ->html('<h2>¡Buen día!</h2><p>Le saludamos del Puerto Pesquero de Anconcito, para indicarle que este es un correo de notificación de pago por lo que deberá acercase a cancelar el pago lo pronto posible.</p>');
    }
}
