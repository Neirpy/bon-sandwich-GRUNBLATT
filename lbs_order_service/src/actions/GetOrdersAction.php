<?php

namespace lbs\order\actions;

use lbs\order\services\OrderService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

class GetOrdersAction
{
  public function __invoke(Request $rq, Response $rs, array $args): Response
    {
      try {
        $args = $rq->getQueryParams();
        $orders = new OrderService();
        $filter= $args['c'] ?? null;
        $c= $args['sort'] ?? null;
        $page= $args['page'] ?? 1;
        $size= $args['size'] ?? 10;
        $orders = $orders->getOrders($filter, $c, $page, $size);
        $data = [
          "type"=>"collection",
          "count"=>$orders['count'],
          "size"=> count($orders['data']),
        ];
        $next = $page+1;
        $prev = $page-1;
        $last = ceil($orders['count']/$size);
        $first = 1;
        if ($page >= $last) {
          $next = $last;
          $prev = $last-1;
        }

        foreach ($orders['data'] as $order) {
          $tab = [
              "order"=> $order,
              "links"=> [
                "self"=> [
                    "href"=> "/orders/".$order['id']."/",
                ],
                "next"=> [
                    "href"=> "/orders/?page=".$next."&size=".$size,
                ],
                "prev"=> [
                    "href"=> "/orders/?page=".$prev."&size=".$size,
                ],
                "last"=> [
                    "href"=> "/orders/?page=".$last."&size=".$size,
                ],
                "first"=> [
                    "href"=> "/orders/?page=1&size=".$size,
                ],
              ],
          ];
          $data["orders"][] = $tab;
        }


        $rs = $rs->withHeader("Content-Type", "application/json")->withStatus(200);
      }
      catch (HttpNotFoundException|HttpInternalServerErrorException|HttpMethodNotAllowedException $e) {
        $data = [
          "type"=>"error",
          "status"=>$e->getCode(),
          "message"=> $e->getMessage(),
        ];
        $rs = $rs->withHeader("Content-Type", "application/json")->withStatus($e->getCode());
      }
      $rs->getBody()->write(json_encode($data));
      return $rs;
    }
}