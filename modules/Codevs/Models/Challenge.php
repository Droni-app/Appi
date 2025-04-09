<?php

namespace Modules\Codevs\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Site;
use Modules\Models\Submission;

class Challenge extends Model
{
  public function user()
  {
    return $this->belongsTo(User::class);
  }
  public function site()
  {
    return $this->belongsTo(Site::class);
  }
  public function submissions()
  {
    return $this->hasMany(Submission::class);
  }
}
