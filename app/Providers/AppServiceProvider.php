<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Site;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Gate to manage site permission: only admin enrollment can manage site
    Gate::define('manage-site', function (User $user, Site $site) {
      return $user->enrollments()->where('site_id', $site->id)->where('role', 'admin')->exists();
    });
  }
}
