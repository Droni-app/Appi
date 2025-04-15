<?php

namespace Modules\Learn\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Learn\Models\Lesson;
use Modules\Learn\Models\Registration;
use Modules\Learn\Models\Question;

class Course extends Model
{
  public function lessons() {
    return $this->hasMany(Lesson::class);
  }

  public function registrations() {
    return $this->hasMany(Registration::class);
  }

  public function questions() {
    return $this->hasMany(Question::class);
  }
}
