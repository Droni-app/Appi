<?php

namespace Modules\Learn\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Learn\Models\Course;
use Modules\Learn\Models\Question;
use Modules\Learn\Models\Answer;

class Lesson extends Model
{
  protected $table = 'courses_lessons';

  public function course() {
    return $this->belongsTo(Course::class);
  }
  public function questions() {
    return $this->belongsToMany(Question::class);
  }
  public function answers() {
    return $this->hasMany(Answer::class);
  }
}
