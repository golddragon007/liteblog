<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class user for ORM.
 */
class user extends Model {
  protected $table = 'users';
  protected $fillable = array('id', 'username', 'password', 'email', 'created_at', 'updated_at');
  protected $dateFormat = "Y-m-d H:i:s";
}