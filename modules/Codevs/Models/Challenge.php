<?php

namespace Modules\Codevs\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Site;
use Modules\Codevs\Models\Submission;
use Modules\Codevs\Models\Test;

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
  public function tests()
  {
    return $this->hasMany(Test::class);
  }
  public function submissions()
  {
    return $this->hasMany(Submission::class);
  }
}
