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
    Schema::create('social_comments', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('site_id')->constrained()->onDelete('cascade');
      $table->uuidMorphs('commentable');
      $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('parent_id')->nullable();
      $table->text('content');
      $table->datetime('approved_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('social_comments');
  }
};
