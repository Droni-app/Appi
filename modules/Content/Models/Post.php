<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
  protected $table = 'content_posts';
  //
  public function user() {
    return $this->belongsTo('App\Models\User');
  }
}
