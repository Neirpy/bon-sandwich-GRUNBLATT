<?php

namespace lbs\front\actions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOrderAction
{
  public function __invoke(Request $request, Response $response, $args)
  {
    // Récupération du mail de l'utilisateur et vérification de l'existence de la commande si le mail correspond
    $mail = $request->getAttribute('mail');
    $id = $args['id'];
    $client = new Client(['base_uri' => 'http://api.order.local/']);
    try {
      $response = $client->request('GET', '/orders/' . $id, [
          'headers' => [
              'Authorization' => $request->getHeader('Authorization')[0],
              'Content-Type' => 'application/json'
          ]
      ]);
    } catch (GuzzleException $e) {
      throw new \Exception($e->getMessage());
    }
    $responseBody = json_decode($response->getBody(), true);
    $mailOrder = $responseBody['order']['mail'];
    if ($mailOrder != $mail) {
      $response = new \Slim\Psr7\Response();
      $body = (new \Slim\Psr7\Factory\StreamFactory())->createStream(json_encode(['error' => 'Vous n\'avez pas accès à cette commande']));
      return $response->withStatus(403)
          ->withHeader('Content-Type', 'application/json')
          ->withBody($body);
    }
    return $response;
    // Récupération des informations de la commande

  }
}