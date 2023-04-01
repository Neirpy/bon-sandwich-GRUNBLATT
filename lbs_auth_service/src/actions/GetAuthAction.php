<?php

namespace lbs\auth\actions;
use Exception;
use lbs\auth\services\AuthService;
use MongoDB\Client;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class GetAuthAction
{
  public function __invoke(Request $request, Response $response, $args) :Response
  {
    try {
      if ($request->getHeader('Authorization') == null) {
        throw new \Exception('Missing Authorization header');
      }

      $header = $request->getHeader('Authorization')[0];

      $headToken = sscanf($header, "Bearer %s")[0];

      $service = new AuthService();
      $token = $service->validate($headToken);

      if (is_array($token)) {
        $response->getBody()->write(json_encode([
            $token
        ]));
      }
      else throw new \Exception($token);

      return $response;

    } catch (Exception $e) {
      $response = $response->withStatus(401);
      $response->getBody()->write(json_encode([
          'error' => 'error',
          'code' => 401,
          'message' => $e->getMessage()
      ]));
      return $response;
    }
  }
}