<?php

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Content\Models\Attachment;
use App\Models\User;

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

    Gate::define('manage-attachment', function (User $user, Attachment $attachment) {
      $role = $user->enrollments()->where('site_id', $attachment->site_id)->firstOrfail()->role;
      return $role === 'admin' || $user->id === $attachment->user_id;
    });
  }
}
