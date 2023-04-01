<?php

namespace lbs\order\models;

class Commande extends \Illuminate\Database\Eloquent\Model
{
  protected $table = 'commande';
  protected $primaryKey = 'id';
  public $timestamps = true;
  public $incrementing = false;
  protected $fillable = [
      'client_name',
    // Autres propriétés autorisées pour le mass-assignment
  ];

}