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
    Schema::create('courses_answers', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('lesson_id')->constrained('courses_lessons')->onDelete('cascade');
      $table->string('type')->default('activity');
      $table->longtext('answer')->nullable();
      $table->string('attachments')->nullable();
      $table->string('status')->default('pending');
      $table->datetime('feedback')->nullable();
      $table->integer('qualification')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('courses_answers');
  }
};
