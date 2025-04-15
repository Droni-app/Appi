<?php

namespace Modules\Learn\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Learn\Models\Lesson;

class Answer extends Model
{
  protected $table = 'courses_answers';

  public function lesson() {
    return $this->belongsTo(Lesson::class);
  }
}
