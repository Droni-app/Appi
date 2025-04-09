<?php

namespace Modules\Codevs\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Codevs\Models\Challenge;

class Test extends Model
{
  protected $table = 'codevs_tests';
  public function challenge()
  {
    return $this->belongsTo(Challenge::class);
  }
}
