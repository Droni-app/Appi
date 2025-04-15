<?php

namespace Modules\Learn\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Learn\Models\Course;

class Registration extends Model
{
  protected $table = 'courses_registrations';

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function course() {
    return $this->belongsTo(Course::class);
  }

  public function lessons() {
    return $this->belongsToMany(Lesson::class);
  }
}
