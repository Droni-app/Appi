<?php

namespace Database\Seeders;

use App\Models\Site;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $site = new Site();
    $site->id = '4ebaccf5-b863-4f12-aa49-9bbe0e1844e2';
    $site->name = 'Droni.co';
    $site->domain = 'droni.co';
    $site->description = 'Droni.co | Desarrollo inteligente.';
    $site->provider = 'google';
    $site->provider_client_id = '213131965361-nt6itpml1c9lseihm352j1ab7nmp27k0.apps.googleusercontent.com';
    $site->save();
  }
}
