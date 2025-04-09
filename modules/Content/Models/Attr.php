<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Content\Models\Post;

class Attr extends Model
{
  protected $table = 'content_attrs';
  public $timestamps = false;

  public function post()
  {
    return $this->belongsTo(Post::class);
  }
}
