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
    Schema::create('codevs_challenges', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->string('slug');
      $table->unique(['site_id', 'slug']);
      $table->string('name');
      $table->string('description');
      $table->text('content')->nullable();
      $table->text('scaffold')->nullable();
      $table->string('funcName')->nullable();
      $table->integer('level')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('codevs_challenges');
  }
};
