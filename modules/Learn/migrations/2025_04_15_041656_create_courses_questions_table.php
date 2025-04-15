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
    Schema::create('courses_questions', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('course_id')->constrained()->onDelete('cascade');
      $table->string('name');
      $table->text('question')->nullable();
      $table->string('answer1');
      $table->string('answer2');
      $table->string('answer3');
      $table->string('answer4');
      $table->string('answer5');
      $table->integer('correct')->default(1);
      $table->integer('wons')->default(0);
      $table->integer('loses')->default(0);
      $table->float('rank', 5, 2)->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('courses_questions');
  }
};
