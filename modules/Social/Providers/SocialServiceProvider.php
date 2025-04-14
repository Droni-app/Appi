<?php

namespace Modules\Social\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Site;

class SocialServiceProvider extends ServiceProvider
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

    Gate::define('manage-social', function (User $user, Site $site) {
      $role = $user->enrollments()->where('site_id', $site->id)->firstOrfail()->role;
      return $role === 'admin';
    });
  }
}
