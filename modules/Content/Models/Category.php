<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $table = 'content_categories';
  //
  public function user() {
    return $this->belongsTo('App\Models\User');
  }
}
