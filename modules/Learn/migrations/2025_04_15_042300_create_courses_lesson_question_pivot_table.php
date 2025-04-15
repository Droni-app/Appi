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
    Schema::create('courses_lesson_question', function (Blueprint $table) {
      $table->foreignId('lesson_id')->constrained('courses_lessons')->onDelete('cascade');
      $table->foreignId('question_id')->constrained('courses_questions')->onDelete('cascade');
      $table->unique(['lesson_id', 'question_id']);
      $table->primary(['lesson_id', 'question_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('courses_lesson_question');
  }
};
