<?php

namespace lbs\order\actions;

use lbs\order\services\OrderService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

class PutOrderAction
{
  public function __invoke(Request $rq, Response $rs, array $args): Response
    {
      $id = $args['id'];
      try{
        $body = $rq->getParsedBody();
        if ($body == null) {
          throw new HttpMethodNotAllowedException($rq, "Mauvaise requête");
        }

        if (gettype($body) != "array") {
          throw new HttpMethodNotAllowedException($rq, "Mauvaise requête");
        }

        //vérifier que le body contient uniquement les champs mail, livraison et nom à modifier
        $keys = array_keys($body);
        $keys = array_diff($keys, ['mail', 'livraison', 'nom']);
        if (count($keys) != 0) {
          throw new HttpMethodNotAllowedException($rq, "Mauvaise requête");
        }

        $order = new OrderService();
        $order = $order->updateOrder($id, $body);

        if ($order == null) {
          throw new HttpNotFoundException($rq, "Commande non trouvée");
        }

        if (gettype($order) == "string") {
          throw new HttpMethodNotAllowedException($rq, $order);
        }

        if (gettype($order)=='array' && $order['type'] == "error") {
          throw new HttpInternalServerErrorException($rq, "Erreur interne");
        }

        $response= $rs->withHeader("Content-Type", "application/json")->withStatus(204);

      }
      catch (HttpMethodNotAllowedException|HttpNotFoundException|HttpInternalServerErrorException $e){
        $order = [
          "type"=>"error",
          "error"=>$e->getCode(),
          "message"=> $e->getMessage(),
        ];
        $response= $rs->withHeader("Content-Type", "application/json")->withStatus($e->getCode());
      }
      $response->getBody()->write(json_encode($order));
      return $response;
    }
}