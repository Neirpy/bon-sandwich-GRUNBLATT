<?php

namespace lbs\front\middleware;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Respect\Validation\Validator as v;

class BodyParsingMiddleware
{
  public function __invoke($request, $handler)
  {
    $body = $request->getParsedBody();
    try {
      $v = v::key('client_name', v::stringType()->notEmpty()->length(3))
          ->key('client_mail', v::email())
          ->key('delivery', v::key('date', v::date('d-m-Y')))
          ->key('items', v::arrayType()->notEmpty()->each(
              v::key('name', v::stringType()->notEmpty()),
              v::key('uri', v::url()),
              v::key('quantity', v::intType()->positive()),
              v::key('price', v::intType()->positive())
          ));
      if (!$v->validate($body)) {
        throw new Exception('Les données reçues sont invalides');
      }
      $request = $request->withAttribute('body', $body);
      return $handler->handle($request);
    } catch (GuzzleException $e) {
      throw new Exception($e->getMessage());
    }

  }
}