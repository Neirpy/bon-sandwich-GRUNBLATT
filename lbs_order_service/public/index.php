<?php
declare(strict_types=1);

use lbs\order\actions\CreateOrderAction;
use lbs\order\actions\GetOrderAction;
use lbs\order\actions\GetOrderItemsAction;
use lbs\order\actions\GetOrdersAction;
use lbs\order\actions\HomeAction;
use lbs\order\actions\PutOrderAction;
use lbs\order\errors\renderer\JsonErrorRenderer;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';
//$settings = require_once __DIR__ . '/settings.php';

$ini = parse_ini_file("../conf/order.db.conf.ini");
$db  = new Illuminate\Database\Capsule\Manager();
$db->addConnection($ini);
$db->setAsGlobal();
$db->bootEloquent();


$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware=$app->addErrorMiddleware(true, false, false);
$errorMiddleware->getDefaultErrorHandler()
    ->registerErrorRenderer('application/json',JsonErrorRenderer::class );

//ou forceContentTypes pour forcer le type de contenu

/**
 * configuring API Routes
 */

$app->get('/', HomeAction::class);

$app->get('/orders', GetOrdersAction::class);

$app->get('/orders/{id}', GetOrderAction::class);

$app->post('/orders', CreateOrderAction::class);

//mise à jour d'une commande
$app->put('/orders/{id}', PutOrderAction::class);

// get orders items
$app->get('/orders/{id}/items', GetOrderItemsAction::class);

$app->run();