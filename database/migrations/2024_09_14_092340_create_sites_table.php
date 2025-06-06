<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('sites', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('secret')->nullable();
      $table->string('name');
      $table->string('domain')->unique();
      $table->string('description')->nullable();
      $table->string('logo')->nullable();
      $table->string('icon')->nullable();
      $table->string('provider')->nullable();
      $table->string('provider_client_id')->nullable();
      $table->string('provider_client_secret')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sites');
  }
};
