<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Blogpost object for ORM.
 */
class blogposts extends Model {
  protected $table = 'blogposts';
  protected $fillable = array('id', 'title', 'summary', 'body', 'author', 'created_at', 'updated_at');
  protected $dateFormat = "Y-m-d H:i:s";
}