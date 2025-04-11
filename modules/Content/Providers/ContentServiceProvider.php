<?php

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Content\Models\Attachment;
use App\Models\User;
use App\Models\Site;

class ContentServiceProvider extends ServiceProvider
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

    Gate::define('manage-content', function (User $user, Site $site) {
      $role = $user->enrollments()->where('site_id', $site->id)->firstOrfail()->role;
      return $role === 'admin';
    });
  }
}
