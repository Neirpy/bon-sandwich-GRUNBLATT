<?php

namespace lbs\order\actions;

use lbs\order\services\OrderService;
use Slim\Exception\HttpMethodNotAllowedException;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

class CreateOrderAction
{
    public function __invoke(Request $request, Response $response, $args)
    {
      try {
        $body = $request->getParsedBody();
        $order = new OrderService();
        //verifier que le body contient uniquement les champs mail, livraison et nom
        $keys = array_keys($body);
        $keys = array_diff($keys, ['client_name', 'client_mail', 'delivery','items']);
        if (count($keys) != 0) {
          throw new HttpMethodNotAllowedException($request, "Mauvaise requête");
        }
        //verifier que le body est en json
        if (gettype($body) != "array") {
          throw new HttpMethodNotAllowedException($request, "Mauvaise requête");
        }
        $order = $order->createOrder($body);

        if ($order == null) {
          throw new HttpNotFoundException($request, "Commande non trouvée");
        }

        $response = $response->withHeader("Content-Type", "application/json")->withStatus(201);
        $response->getBody()->write(json_encode($order));
        return $response;

      } catch (HttpMethodNotAllowedException|HttpNotFoundException $e) {
        $order = [
          "type"=>"error",
          "error"=>$e->getCode(),
          "message"=> $e->getMessage(),
        ];
        $response= $response->withHeader("Content-Type", "application/json")->withStatus($e->getCode());
        $response->getBody()->write(json_encode($order));
        return $response;

      }


    }
}