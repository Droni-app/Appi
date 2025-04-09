<?php

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;

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
    $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'content');
  }
}
