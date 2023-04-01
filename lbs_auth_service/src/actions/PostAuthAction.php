<?php

namespace lbs\auth\actions;

use lbs\auth\services\AuthService;
use MongoDB\Client;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PostAuthAction
{
  public function __invoke(Request $request, Response $response, $args): Response
  {
    $headers = $request->getHeaders();
    if (!isset($headers['Authorization'][0])) {
      $response = $response->withStatus(401);
      $response->getBody()->write(json_encode([
          'error' => 'error',
          'code' => 401,
          'message' => 'Missing Authorization header'
      ]));
    }
    $connection = new Client("mongodb://mongo.auth");
    $service = new AuthService();
    $headToken = sscanf($headers['Authorization'][0],"Basic %s")[0];
    $token = $service->signin($connection, $headToken );

    //$token = JWT::decode($token['token'], new Key($token['refresh_token'],'HS256'));
    if (is_array($token)) {
      $response->getBody()->write(json_encode([
          'token' => $token
      ]));
    } else {
      $response = $response->withStatus(401);
      $response->getBody()->write(json_encode([
          'error' => 'error',
          'code' => 401,
          'message' => $token
      ]));
    }


    return $response;
  }

}