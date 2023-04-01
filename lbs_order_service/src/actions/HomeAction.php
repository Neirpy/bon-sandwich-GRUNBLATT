<?php

namespace lbs\order\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

class HomeAction
{
  public function __invoke(Request $rq, Response $rs, array $args): Response
    {
      try {
        $data = [
            'title' => 'Le bon sandwich',
            'status' => 'success'
        ];
        $rs = $rs->withHeader("Content-Type", "application/json")->withStatus(200);
      }
      catch (HttpNotFoundException|HttpInternalServerErrorException|HttpMethodNotAllowedException $e) {
        $data = [
            'title' => 'Le bon sandwich',
            'status' => 'error',
            'message' => $e->getMessage()
        ];
        $rs = $rs->withHeader("Content-Type", "application/json")->withStatus($e->getCode());
      }
        $rs->getBody()->write(json_encode($data));
        return $rs;
    }
}