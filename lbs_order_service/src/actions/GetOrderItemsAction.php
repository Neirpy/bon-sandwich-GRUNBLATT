<?php

namespace lbs\order\actions;
use lbs\order\services\OrderService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

class GetOrderItemsAction
{
  public function __invoke(Request $rq, Response $rs, array $args): Response
    {
      try{
        $id = $args['id'];
        $items = new OrderService();
        $items = $items->getItems($id);
        if (count($items) == 0) {
          throw new HttpNotFoundException($rq, "Items not found");
        }

        $result=[
            "type"=>"collection",
            "count"=> count($items),
            "items"=> $items->toArray(),
        ];
        $response= $rs->withHeader("Content-Type", "application/json")->withStatus(200);
      }
      catch (HttpInternalServerErrorException|HttpNotFoundException|HttpMethodNotAllowedException $e){
        $result = [
          "type"=>"error",
          "error"=>$e->getCode(),
          "message"=> $e->getMessage(),
        ];
        $response= $rs->withHeader("Content-Type", "application/json")->withStatus($e->getCode());
      }
      $response->getBody()->write(json_encode($result));
      return $response;
    }
}