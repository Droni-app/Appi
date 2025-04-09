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
    Schema::create('codevs_submissions', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('challenge_id')->constrained('codevs_challenges')->cascadeOnDelete();
      $table->text('code');
      $table->boolean('complete')->default(false);
      $table->decimal('complete_time', 8, 4)->default(0);
      $table->float('rank', 8, 4)->default(0);
      $table->integer('votes')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('codevs_submissions');
  }
};
