<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Content\Models\Category;
use Modules\Content\Models\Attr;


class Post extends Model
{
  protected $table = 'content_posts';

  public function user() {
    return $this->belongsTo(User::class);
  }
  public function category() {
    return $this->belongsTo(Category::class);
  }
  public function attrs() {
    return $this->hasMany(Attr::class);
  }
}
