<?php
declare(strict_types=1);

use lbs\order\errors\renderer\JsonErrorRenderer;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';
//$settings = require_once __DIR__ . '/settings.php';

$ini = parse_ini_file("../conf/order.db.conf.ini");
$db  = new Illuminate\Database\Capsule\Manager();
$db->addConnection($ini);
$db->setAsGlobal();
$db->bootEloquent();


$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware=$app->addErrorMiddleware(true, false, false);
$errorMiddleware->getDefaultErrorHandler()
    ->registerErrorRenderer('application/json',JsonErrorRenderer::class );

//ou forceContentTypes pour forcer le type de contenu

/**
 * configuring API Routes
 */

$app->get('/home', function (Request $request, Response $response) {
  $data = [
    'title' => 'Le bon sandwich',
    'status' => 'success'
  ];
  $response= $response->withHeader("Content-Type", "application/json")->withStatus(200);
  $response->getBody()->write(json_encode($data));
  return $response;
});

$app->get('/orders', function (Request $request, Response $response, $args) {
  $data =[
    "type"=>"collection",
    "count"=> 3,
    "orders"=> \lbs\order\models\Commande::select('id','nom','mail')->take(3)->get()->toArray(),
  ];
  $response= $response->withHeader("Content-Type", "application/json")->withStatus(200);
  $response->getBody()->write(json_encode($data));
  return $response;
});

$app->get('/orders/{id}', function (Request $request, Response $response, $args) {
  try {
    $data =\lbs\order\models\Commande::select()->where('id','like', $args['id'])->firstOrFail();
  } catch (Exception $e) {
    throw new HttpNotFoundException($request, "request not found : " . $request->getUri()->getPath());
  }
  $response= $response->withHeader("Content-Type", "application/json")->withStatus(200);

  $result=[
      "type"=>"ressource",
      "order"=> [
          "id"=> $data['id'],
          "client_mail"=> $data['mail'],
          "client_name"=> $data['nom'],
          "montant"=> (float)$data['montant'],
          "delivery_date"=>  $data['livraison'],
      ]
  ];
  $response->getBody()->write(json_encode($result));
  return $response;
});

//mise à jour d'une commande
$app->put('/orders/{id}', function (Request $request, Response $response, $args) {
  $data = $request->getParsedBody() ?? throw new HttpNotImplementedException($request, "request not implemented : " . $request->getUri()->getPath());
  $orderService = new OrderManagementService();
  try{
    $orderService->updateOrder($args['id'], $request->getParsedBody());
    $response= $response->withHeader("Content-Type", "application/json")->withStatus(200);
    $response->getBody()->write(json_encode(['status' => 'success']));
    return $response;
  }
  catch (Exception $e){
    throw new HttpNotImplementedException($request, "request not implemented : " . $request->getUri()->getPath());
  }
});

$app->run();