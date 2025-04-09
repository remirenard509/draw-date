<?php

// use Dotenv\Dotenv;

// $dotenv = Dotenv::createImmutable(dirname(__DIR__));

// $dotenv->load();

// $servername = getenv('DB_HOST');
// $username = getenv('DB_USER');
// $password = getenv('DB_PASSWORD');
// $dbname = getenv('DB_NAME');
// $port = getenv('DB_PORT');

// $conn = new mysqli($servername, $username, $password, $dbname, $port);

// if ($conn->connect_error) {
//     die("Connexion échouée : " . $conn->connect_error);
// }
// "Connexion réussie à la base de données !";

use App\Router;
use App\Controllers\{User, Auth};

try {
    require_once __DIR__ . '/../vendor/autoload.php';

    $controllers = [
        User::class,
        Auth::class,
    ];

    $router = new Router();
    $router->registerControllers($controllers);
    $router->run();
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    error_log("🔥 Exception: " . $e->getMessage());
}
?>
