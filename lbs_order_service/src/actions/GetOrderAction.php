<?php

namespace lbs\order\actions;

use lbs\order\services\OrderService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

class GetOrderAction
{
  public function __invoke(Request $rq, Response $rs, array $args): Response
    {
      try {
        $order = new OrderService();
        if(!isset($args["id"]))
          throw new \Slim\Exception\HttpMethodNotAllowedException($rq, "bad request : id param is empty");
        $order = $order->getOrder($args['id'], $args['embed'] ?? null);
        if ($order == null) {
          throw new HttpNotFoundException($rq, "Order not found");
        }

        $data = [
          "type"=>"resource",
          "order"=> $order,
          "links"=> [
            "self"=> [
              "href"=> "/orders/".$args['id'],
            ],
            "items"=> [
              "href"=> "/orders/".$args['id']."/items",
            ],
          ],
        ];


        $rs = $rs->withHeader("Content-Type", "application/json")->withStatus(200);
      }
      catch (HttpNotFoundException|HttpInternalServerErrorException|HttpMethodNotAllowedException $e) {
        $data = [
          "type"=>"error",
          "error"=>$e->getCode(),
          "message"=> $e->getMessage(),
        ];
        $rs = $rs->withHeader("Content-Type", "application/json")->withStatus($e->getCode());
      }
      $rs->getBody()->write(json_encode($data));
      return $rs;
    }
}