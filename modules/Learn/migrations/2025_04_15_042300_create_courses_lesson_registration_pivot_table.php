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
    Schema::create('courses_lesson_registration', function (Blueprint $table) {
      $table->foreignId('lesson_id')->constrained('courses_lessons')->onDelete('cascade');
      $table->foreignId('registration_id')->constrained('courses_registrations')->onDelete('cascade');
      $table->unique(['lesson_id', 'registration_id']);
      $table->primary(['lesson_id', 'registration_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('courses_lesson_registration');
  }
};
