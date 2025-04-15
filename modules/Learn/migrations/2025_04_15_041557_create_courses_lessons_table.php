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
    Schema::create('courses_lessons', function (Blueprint $table) {
      $table->id();
      $table->foreignId('course_id')->constrained()->onDelete('cascade');
      $table->integer('position')->default(0);
      $table->string('type')->default('document');
      $table->string('name');
      $table->string('slug');
      $table->unique(['course_id', 'slug']);
      $table->string('video')->nullable();
      $table->string('live')->nullable();
      $table->date('live_date')->nullable();
      $table->text('description')->nullable();
      $table->longtext('content')->nullable();
      $table->boolean('is_activity')->default(false);
      $table->date('limit_date')->nullable();
      $table->boolean('active')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('courses_lessons');
  }
};
