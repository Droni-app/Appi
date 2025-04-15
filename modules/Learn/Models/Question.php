<?php

namespace Modules\Learn\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Learn\Models\Course;
use App\Models\User;
use Modules\Learn\Models\Lesson;

class Question extends Model
{
  protected $table = 'courses_questions';

  public function course() {
    return $this->belongsTo(Course::class);
  }

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function lessons() {
    return $this->belongsToMany(Lesson::class);
  }
}
