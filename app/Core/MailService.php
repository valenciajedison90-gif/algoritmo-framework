<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Servicio Central de Envío de Correos Electrónicos de Algoritmo Framework.
 * Reemplaza de forma segura la implementación legada de MBLL_Correo.
 */
class MailService
{
    protected LogService $logger;

    public function __construct(LogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Envía un correo electrónico HTML o de texto plano.
     *
     * @param string|array $destinatario Correo electrónico o lista de correos
     * @param string $asunto Asunto del mensaje
     * @param string $mensaje Contenido HTML o texto del mensaje
     * @param array $adjuntos Rutas de archivos a adjuntar ['ruta' => 'nombre']
     * @param array $cc Copias
     * @return array Formato estandarizado de respuesta
     */
    public function enviar(
        string|array $destinatario,
        string $asunto,
        string $mensaje,
        array $adjuntos = [],
        array $cc = []
    ): array {
        try {
            Mail::html($mensaje, function ($mail) use ($destinatario, $asunto, $adjuntos, $cc) {
                $mail->to($destinatario)
                    ->subject($asunto);

                if (!empty($cc)) {
                    $mail->cc($cc);
                }

                foreach ($adjuntos as $ruta => $nombre) {
                    if (is_numeric($ruta)) {
                        $mail->attach($nombre);
                    } else {
                        $mail->attach($ruta, ['as' => $nombre]);
                    }
                }
            });

            $this->logger->info("Correo enviado exitosamente", [
                'asunto' => $asunto,
                'destinatario' => is_array($destinatario) ? implode(', ', $destinatario) : $destinatario,
            ]);

            return ResponseHelper::exitoso("Correo enviado correctamente.");
        } catch (Throwable $e) {
            $this->logger->error("Fallo al enviar correo: " . $e->getMessage(), [
                'asunto' => $asunto,
                'destinatario' => $destinatario,
                'excepcion' => $e->getTraceAsString(),
            ]);

            return ResponseHelper::error("No fue posible enviar el correo: " . $e->getMessage());
        }
    }

    /**
     * Envía un correo renderizado a partir de una vista Blade.
     */
    public function enviarVista(
        string|array $destinatario,
        string $asunto,
        string $vista,
        array $datosVista = [],
        array $adjuntos = []
    ): array {
        try {
            Mail::send($vista, $datosVista, function ($mail) use ($destinatario, $asunto, $adjuntos) {
                $mail->to($destinatario)
                    ->subject($asunto);

                foreach ($adjuntos as $ruta => $nombre) {
                    if (is_numeric($ruta)) {
                        $mail->attach($nombre);
                    } else {
                        $mail->attach($ruta, ['as' => $nombre]);
                    }
                }
            });

            return ResponseHelper::exitoso("Correo con plantilla enviado correctamente.");
        } catch (Throwable $e) {
            $this->logger->error("Fallo al enviar correo con plantilla Blade: " . $e->getMessage(), [
                'vista' => $vista,
                'destinatario' => $destinatario,
            ]);

            return ResponseHelper::error("Error al enviar el correo: " . $e->getMessage());
        }
    }
}
