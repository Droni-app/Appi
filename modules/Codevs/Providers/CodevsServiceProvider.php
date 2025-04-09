<?php

namespace Modules\Codevs\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Modules\Codevs\Models\Challenge;

class CodevsServiceProvider extends ServiceProvider
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

    Gate::define('manage-challenge', function (User $user, Challenge $challenge) {
      $role = $user->enrollments()->where('site_id', $challenge->site_id)->firstOrfail()->role;
      return $role === 'admin' || $user->id === $challenge->user_id;
    });
  }
}
