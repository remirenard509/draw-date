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
// envoie un mail pour récupérer le mot de passe
    #[Route("POST", "/sendmail")]
 public function sendmail() {
    try {
        $data = $this->body;

        if (!isset($data['Email'], $data['Name'], $data['Subject'])) {
            throw new \Exception("Paramètres requis manquants");
        }

        $to_email = $data['Email'];
        $to_name = $data['Name'];
        $subject = $data['Subject'];
        $content = $data['Content'];

        return $this->send($to_email, $to_name, $subject, $content);
    } catch (\Exception $e) {
        error_log('Erreur dans sendmail() : ' . $e->getMessage());
        throw new HttpException("An unexpected error occurred.", 500);
    }
}

// lié à la fonction sendmail
public function send($to_email, $to_name, $subject, $content)
{
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
                'TextPart' => strip_tags($content),
            ]
        ]
    ];

    try {
        $response = $mj->post(Resources::$Email, ['body' => $body]);
    } catch (\Exception $e) {
        throw new HttpException("Erreur lors de l'envoi de l'email.", 500);
    }
  }
}
