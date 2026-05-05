<?php

declare(strict_types=1);

use App\Controllers\GraphController;

require __DIR__ . '/../vendor/autoload.php';

$controller = new GraphController();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET' && $path === '/') {
    $controller->index();
    exit;
}

if ($method === 'POST' && $path === '/node') {
    $controller->storeNode();
    exit;
}

if ($method === 'POST' && $path === '/relationship') {
    $controller->storeRelationship();
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Rota não encontrada.';
