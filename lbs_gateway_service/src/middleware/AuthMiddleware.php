<?php

namespace lbs\front\middleware;

use GuzzleHttp\Exception\GuzzleException;

class AuthMiddleware
{
  private \GuzzleHttp\Client $httpClient;

  public function __construct()
  {
    $this->httpClient = new \GuzzleHttp\Client(['base_uri' => 'http://api.auth.local/']);
  }

  public function __invoke($request, $handler)
  {
$token = $request->getHeader('Authorization')[0];
    try {
      $response = $this->httpClient->request('GET', '/validate', [
          'headers' => [
              'Authorization' => $token,
              'Content-Type' => 'application/json'
          ]
      ]);
    } catch (GuzzleException $e) {
      throw new \Exception($e->getMessage());
    }
    $responseBody = json_decode($response->getBody(), true);
    $mail = $responseBody[0]['mail'];
    $roles = $responseBody[0]['level'];
    $request = $request->withAttribute('mail', $mail);
    $request= $request->withAttribute('roles', $roles);
    return $handler->handle($request);
  }

}