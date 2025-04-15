<?php

namespace Modules\Learn\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Site;
use Modules\Learn\Models\Course;
class LearnServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application services.
   *
   * @return void
   */
  public function boot()
  {
    $this->loadMigrationsFrom(__DIR__.'/../migrations');
    $this->loadRoutesFrom(__DIR__ . '/../routes.php');

    Gate::define('manage-learn', function (User $user, Site $site) {
      $role = $user->enrollments()->where('site_id', $site->id)->firstOrfail()->role;
      return $role === 'admin';
    });

    Gate::define('manage-course', function (User $user, Course $course) {
      $role = $user->enrollments()->where('site_id', $course->site->id)->firstOrfail()->role;
      $isTeacher = $course->user_id === $user->id;
      return $role === 'admin' || $isTeacher;
    });

    Gate::define('use-course', function (User $user, Course $course) {
      $isAdmin = $user->enrollments()->where('site_id', $course->site->id)->firstOrfail()->role === 'admin';
      $isTeacher = $course->user_id === $user->id;
      $isStudent = $course->registrations()->where('user_id', $user->id)->exists();
      return $isAdmin || $isStudent || $isTeacher;
    });
  }
}
