<?php

declare(strict_types=1);

use App\Controllers\GraphController;

$autoload = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');

    echo <<<'HTML'
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dependências não instaladas</title>
    <style>
      body { font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 2rem; }
      .card { max-width: 860px; margin: 0 auto; background: #111827; border: 1px solid #334155; border-radius: 12px; padding: 1.5rem; }
      code { background: #1f2937; padding: 0.2rem 0.4rem; border-radius: 6px; }
      pre { background: #020617; padding: 1rem; border-radius: 10px; overflow-x: auto; }
      h1 { margin-top: 0; }
    </style>
  </head>
  <body>
    <div class="card">
      <h1>Erro: dependências do Composer não encontradas.</h1>
      <p>O arquivo <code>vendor/autoload.php</code> não existe.</p>
      <p>No diretório raiz do projeto, execute:</p>
      <pre>composer install</pre>
      <p>Depois disso, inicie novamente o servidor PHP (exemplo):</p>
      <pre>php -S localhost:8000 -t public</pre>
      <p>Este projeto usa Neo4j via pacote Composer <code>laudis/neo4j-php-client</code>.</p>
    </div>
  </body>
</html>
HTML;
    exit;
}

require $autoload;

$controller = new GraphController();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

if ($baseDir !== '' && $baseDir !== '/' && str_starts_with($path, $baseDir)) {
    $path = substr($path, strlen($baseDir)) ?: '/';
}

if (str_starts_with($path, '/index.php')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}

$path = '/' . ltrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET' && $path === '/') {
    $controller->index();
    exit;
}

if ($method === 'GET' && $path === '/schema') {
    $controller->schema();
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

if ($method === 'GET' && $path === '/relationship-types') {
    $controller->relationshipTypes();
    exit;
}

if ($method === 'GET' && $path === '/node-names') {
    $controller->nodeNames();
    exit;
}

if ($method === 'GET' && $path === '/schema-items') {
    $controller->schemaItems();
    exit;
}

if ($method === 'POST' && $path === '/schema-items') {
    $controller->storeSchemaItem();
    exit;
}

if ($method === 'POST' && $path === '/schema-items/update') {
    $controller->updateSchemaItem();
    exit;
}

if ($method === 'POST' && $path === '/schema-items/delete') {
    $controller->deleteSchemaItem();
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Rota não encontrada.';
