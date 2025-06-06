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
    Schema::create('courses', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('site_id')->constrained()->onDelete('cascade');
      $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
      $table->string('category')->nullable();
      $table->string('slug');
      $table->unique(['site_id', 'slug']);
      $table->string('name');
      $table->string('icon')->nullable();
      $table->string('picture')->nullable();
      $table->string('video')->nullable();
      $table->text('description')->nullable();
      $table->boolean('open')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('courses');
  }
};
