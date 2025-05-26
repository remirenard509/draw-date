<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Router;
use App\Controllers\{User, Auth, Chat, MailController};
 require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
try {
   

    $controllers = [
        User::class,
        Auth::class,
        Chat::class,
        MailController::class
    ];

    $router = new Router();
    $router->registerControllers($controllers);
    $router->run();
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    error_log(" Exception: " . $e->getMessage());
}
?>
