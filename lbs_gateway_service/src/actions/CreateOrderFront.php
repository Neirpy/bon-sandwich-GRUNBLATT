<?php

namespace lbs\front\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateOrderFront
{
    public function __invoke(Request $rq, Response $rs, $args)
    {
        $body = $rq->getAttribute('body');
        $roles = $rq->getAttribute('roles');
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://api.order.local/']);
        try {
          if ($roles>=10) {
            $response = $client->request('POST', '/orders', [
                'headers' => [
                    'Authorization' => $rq->getHeader('Authorization')[0],
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'client_name' => $body['client_name'],
                    'client_mail' => $body['client_mail'],
                    'delivery' => $body['delivery'],
                    'items' => $body['items']
                ]
            ]);
            $responseBody = json_decode($response->getBody(), true);
            $rs = $rs->withHeader("Content-Type", "application/json")->withStatus(201);
            $rs->getBody()->write(json_encode($responseBody));
          } else {
            $rs = $rs->withHeader("Content-Type", "application/json")->withStatus(403);
            $rs->getBody()->write(json_encode(['error' => 'Vous n\'avez pas les droits pour effectuer cette action']));
          }
          return $rs;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $rs = $rs->withHeader("Content-Type", "application/json")->withStatus(400);
            $rs->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $rs;
        }
    }
}