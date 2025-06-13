<?php 
// permet du vérifier le token d'un utilisateur
namespace App\Middlewares;

use App\Utils\JWT;

class AuthMiddleware {
    public function handle($request) {
        try {
            $headers = getallheaders();

            // Vérifiez si le header Authorization est présent
            if (!isset($headers['Authorization'])) {
                throw new \Exception("Authorization header missing", 401);
            }


            $authHeader = $headers['Authorization'];

            // Vérifiez si le header Authorization contient un token Bearer
            if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                throw new \Exception("Invalid Authorization header format", 401);
            }

            $jwt = $matches[1];
            $id = $headers['user-id'];

            // Vérifiez la validité du JWT
            if (!JWT::verify($jwt)) {
                throw new \Exception("Invalid or expired token", 401);
            }
            if(isset($headers['user-id'])) {
                return JWT::validateWithIdAndExpiry($jwt, $id);
            }

            // Si tout est valide, continuez la requête
            return true;
        } catch (\Exception $e) {
            // Gérez les erreurs et retournez une réponse appropriée
            $this->unauthorizedResponse($e->getMessage());
            return false;
        }
    }

    // Méthode helper pour retourner une réponse 401 Unauthorized
    private function unauthorizedResponse($message) {
        echo json_encode(['error' => $message]);
        http_response_code(401);
    }
}