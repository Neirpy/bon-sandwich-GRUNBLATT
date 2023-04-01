<?php

namespace lbs\order\models;

class Item extends \Illuminate\Database\Eloquent\Model
{
  protected $table = 'item';
  protected $primaryKey = 'id';
  public $timestamps = false;
  public $incrementing = true;
}