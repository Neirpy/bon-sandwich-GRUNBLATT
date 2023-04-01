<?php
declare(strict_types=1);

use lbs\front\actions\GetOrderAction;
use lbs\front\actions\CreateOrderFront;
use lbs\front\actions\PostSignin;
use lbs\front\errors\renderer\JsonErrorRenderer;
use lbs\front\middleware\AuthMiddleware;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware=$app->addErrorMiddleware(true, false, false);
$errorMiddleware->getDefaultErrorHandler()
    ->registerErrorRenderer('application/json',JsonErrorRenderer::class );


$app->post('/signin', PostSignin::class);

//commande d'accès à une commande avec le middleware d'authentification
$app->get('/orders/{id}', GetOrderAction::class)
    ->add(AuthMiddleware::class);

$app->post('/orders', CreateOrderFront::class)
    ->add(AuthMiddleware::class)
    ->add(new \lbs\front\middleware\BodyParsingMiddleware);

$app->run();

