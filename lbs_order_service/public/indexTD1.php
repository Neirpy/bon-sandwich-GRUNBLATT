<?php
declare(strict_types=1);
use Slim\Factory\AppFactory;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../vendor/autoload.php';
//$settings = require_once __DIR__ . '/settings.php';


$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false);

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
    "orders"=> [
      [
          "id"=> "AuTR4-65ZTY",
          "client_mail"=> "jan.neymar@yaboo.fr",
          "order_date"=> "2022-01-05 12:00:23",
          "total_amount"=> 25.95
      ],
      [
          "id"=> "657GT-I8G443",
          "client_mail"=> "jan.neplin@gmal.fr",
          "order_date"=> "2022-01-06 16:05:47",
          "total_amount"=> 42.95

      ],
      [
          "id"=> "K9J67-4D6F5",
          "client_mail"=> "claude.francois@grorange.fr",
          "order_date"=> "2022-01-07 17:36:45",
          "total_amount"=> 14.95
      ]
    ]

  ];
  $response= $response->withHeader("Content-Type", "application/json")->withStatus(200);
  $response->getBody()->write(json_encode($data));
  return $response;
});

$app->get('/orders/{id}', function (Request $request, Response $response, $args) {
  $data =[
      "type"=>"collection",
      "count"=> 3,
      "orders"=> [
          [
              "id"=> "AuTR4-65ZTY",
              "client_mail"=> "jan.neymar@yaboo.fr",
              "order_date"=> "2022-01-05 12:00:23",
              "total_amount"=> 25.95
          ],
          [
              "id"=> "657GT-I8G443",
              "client_mail"=> "jan.neplin@gmal.fr",
              "order_date"=> "2022-01-06 16:05:47",
              "total_amount"=> 42.95

          ],
          [
              "id"=> "K9J67-4D6F5",
              "client_mail"=> "claude.francois@grorange.fr",
              "order_date"=> "2022-01-07 17:36:45",
              "total_amount"=> 14.95
          ]
      ]

  ];
  $response= $response->withHeader("Content-Type", "application/json")->withStatus(200);
  foreach ($data['orders'] as $order){
    if($order['id'] == $args['id']){
      $result=[
          "type"=>"ressource",
          "order"=> [
              "id"=> $order['id'],
              "client_mail"=> $order['client_mail'],
              "order_date"=> $order['order_date'],
              "delivery_date"=> date("Y-m-d H:i:s"),
              "total_amount"=> $order['total_amount']
          ]
          ];
      $response->getBody()->write(json_encode($result));
      return $response;
    }
  }
});


$app->run();
