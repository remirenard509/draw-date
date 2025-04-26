<?php

use App\Router;
use App\Controllers\{User, Auth, Chat};

try {
    require_once __DIR__ . '/../vendor/autoload.php';

    $controllers = [
        User::class,
        Auth::class,
        Chat::class
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
