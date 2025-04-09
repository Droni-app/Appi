<?php

namespace Modules\Codevs\Models;
use Modules\Codevs\Models\Challenge;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
  protected $table = 'codevs_submissions';

  public function user()
  {
    return $this->belongsTo(User::class);
  }
  public function challenge()
  {
    return $this->belongsTo(Challenge::class);
  }
}
