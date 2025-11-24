<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$router = $app->make(Illuminate\Routing\Router::class);
print_r($router->getMiddleware());
