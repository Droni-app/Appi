<?php

namespace Modules\Social\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Site;
use App\Models\User;

class Comment extends Model
{
  public $table = 'social_comments';

  public function commentable(): MorphTo
  {
    return $this->morphTo();
  }
  public function site()
  {
    return $this->belongsTo(Site::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function parent()
  {
    return $this->belongsTo(Comment::class, 'parent_id');
  }

  public function children()
  {
    return $this->hasMany(Comment::class, 'parent_id');
  }
}
