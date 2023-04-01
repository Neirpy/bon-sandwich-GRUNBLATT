<?php

namespace lbs\order\services;

use Exception;
use lbs\order\models\Item;
use lbs\order\models\Commande;

class OrderService
{
  public function getOrders(?string $filter=null, ?string $sort=null, ?int $page=1, ?int $size=10){
    try {
      $orders = Commande::select('id', 'nom', 'mail', 'created_at', 'livraison', 'status');
      if (!is_null($filter)) $orders = $orders->where('mail', 'like', $filter);
      if (!is_null($sort)) {
        $orders = $orders->orderBy(self::sortResult($sort)[0], self::sortResult($sort)[1]);
      }

      $count = $orders->count();
      $lastPage = ceil($count/$size);
      if ($page > $lastPage) $page = $lastPage;
      $orders = $orders->skip(($page-1)*$size)->take($size)->get()->toArray();


      return ['data'=>$orders, 'count'=>$count];
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  protected function sortResult(string $sort){
    if ($sort == 'amount'){
      return ['montant', 'desc'];
    }
    if ($sort == 'date'){
      return ['created_at', 'desc'];
    }
  }
  public function getOrder(string $id, ?string $embed=null){
    try {
      $order= Commande::select('id', 'nom', 'mail', 'created_at')->find($id)->toArray();
    }
    catch (Exception $e) {
      return $e->getMessage();
    }
    if ($embed == 'items') {
      try {
        $items = Item::select()->where('command_id', 'like', $id)->get()->toArray();
        $order[0]['items'] = $items;
      }
      catch (Exception $e) {
        return $e->getMessage();
      }
    }
    return $order;
  }

  public function getItems($id){
    try {
      return Item::select()->where('command_id','like', $id)->get();
    }
    catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public function createOrder($data){
    try {
      $commande = new Commande();
      $data = filter_var_array($data, FILTER_SANITIZE_ENCODED);
      $dateString = $data['delivery']['date'];
      $time = $data['delivery']['time'];
      $date = \DateTime::createFromFormat('d-m-Y', $dateString);
      $formattedDate = $date->format('Y-m-d');
      $new_data = [
          "order"=>[
            'client_name' => urldecode($data['client_name']),
            'client_mail' => urldecode($data['client_mail']),
            'livraison' => $formattedDate . ' ' . urldecode($time),
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'total_amount' => 0,
          ]
      ];

      $items = $data['items'];
      foreach ($items as $item) {
        $new_item = new Item();
        $new_item->command_id = $new_data['order']['id'];
        $new_item->libelle = $item['name'];
        $new_item->uri = $item['uri'];
        $new_item->quantite = intval($item['q']);
        $new_item->tarif = intval($item['price']);
        $new_item->save();
        $new_data['order']['total_amount'] += $item['q'] * $item['price'];
      }

      $commande->id = $new_data['order']['id'];
      $commande->nom = urldecode($new_data['order']['client_name']);
      $commande->mail = urldecode($new_data['order']['client_mail']);
      $commande->livraison = urldecode($new_data['order']['livraison']);
      $commande->montant = $new_data['order']['total_amount'];
      $commande->save();
      return $new_data;

    }
    catch (Exception $e) {
      return [
          "type"=>"error",
          "error"=>$e->getCode(),
          "message"=> $e->getMessage(),
      ];
    }
  }

  public function updateOrder($id, $data){
    try {
      $data = filter_var_array($data, FILTER_SANITIZE_ENCODED);
      return Commande::where('id', $id)->update($data);
    }
    catch (Exception $e) {
      return [
          "type"=>"error",
          "error"=>$e->getCode(),
          "message"=> $e->getMessage(),
      ];
    }
  }
}