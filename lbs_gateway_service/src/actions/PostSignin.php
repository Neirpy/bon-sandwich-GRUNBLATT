<?php

namespace lbs\front\actions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PostSignin
{
  public function __invoke(Request $request, Response $response, $args): Response
  {
    $client = new Client(['base_uri' => 'http://api.auth.local/']);
    try {
      $response = $client->request('POST', '/signin', [
          'headers' => [
              'Authorization' => $request->getHeader('Authorization')[0],
              'Content-Type' => 'application/json'
          ]
      ]);
    } catch (GuzzleException $e) {
      throw new \Exception($e->getMessage());
    }
    $responseBody = json_decode($response->getBody(), true);
    $token = $responseBody['token'];
    $response = $response->withHeader('Authorization', $token);
    return $response;


  }
}