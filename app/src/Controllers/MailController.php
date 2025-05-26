<?php

namespace App\Controllers;

use Mailjet\Client;
use Mailjet\Resources;
use App\Controllers\Controller;
use App\Utils\Route;
use App\Utils\HttpException;

class MailController extends Controller {
    public function __construct($param) {
      parent::__construct($param);
    }

    #[Route("POST", "/sendmail")]
 public function sendmail() {
    try {
        error_log("Début méthode sendmail");
        $data = $this->body;

        if (!isset($data['Email'], $data['Name'], $data['Subject'])) {
            error_log("Paramètres manquants : " . json_encode($data));
            throw new \Exception("Paramètres requis manquants");
        }

        $to_email = $data['Email'];
        $to_name = $data['Name'];
        $subject = $data['Subject'];
        $content = $data['Content'];

        error_log("Avant envoi Mailjet : $to_email, $to_name, $subject, $content");

        $this->send($to_email, $to_name, $subject, $content);
        return true;
    } catch (\Exception $e) {
        error_log('Erreur dans sendmail() : ' . $e->getMessage());
        throw new HttpException("An unexpected error occurred.", 500);
    }
}


public function send($to_email, $to_name, $subject, $content)
{
    // Initialisation du client Mailjet
    $mj = new Client(
        $_ENV['MJ_APIKEY_PUBLIC'] ?? null,
        $_ENV['MJ_APIKEY_PRIVATE'] ?? null,
        true,
        ['version' => 'v3.1']
    );

    if (!$mj) {
        throw new \Exception("Mailjet client non instancié");
    }

    $body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => "remi.renard@coda-student.school",
                    'Name' => "draw date"
                ],
                'To' => [
                    [
                        'Email' => $to_email,
                        'Name' => $to_name
                    ]
                ],
                'Subject' => $subject,
                'TextPart' => strip_tags($content), // optionnel, version texte
            ]
        ]
    ];

    // 🧩 Log de debug
    error_log("📧 Tentative d'envoi via Mailjet à $to_email ($to_name)");

    try {
        $response = $mj->post(Resources::$Email, ['body' => $body]);

        // 🧩 Log de la réponse si nécessaire
        error_log("✅ Mailjet réponse : " . $response->getStatus() . " - " . json_encode($response->getData()));
    } catch (\Exception $e) {
        error_log("❌ Erreur d'envoi Mailjet : " . $e->getMessage());
        throw new HttpException("Erreur lors de l'envoi de l'email.", 500);
    }
  }
}
