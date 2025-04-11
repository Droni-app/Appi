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
    Schema::create('content_attrs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('post_id')->constrained('content_posts')->onDelete('cascade');
      $table->string('name');
      $table->string('type')->default('string');
      $table->string('value');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('content_attrs');
  }
};
