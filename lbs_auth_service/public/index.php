<?php
declare(strict_types=1);

use lbs\auth\errors\renderer\JsonErrorRenderer;
use MongoDB\Client;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$connection = new Client("mongodb://mongo.auth");

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware=$app->addErrorMiddleware(true, false, false);
$errorMiddleware->getDefaultErrorHandler()
    ->registerErrorRenderer('application/json',JsonErrorRenderer::class );

// Route http basique auth
$app->post('/signin', \lbs\auth\actions\PostAuthAction::class);
$app->get('/validate', \lbs\auth\actions\GetAuthAction::class);

$app->run();