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
    Schema::create('codevs_tests', function (Blueprint $table) {
      $table->id();
      $table->foreignId('challenge_id')->constrained('codevs_challenges')->cascadeOnDelete();
      $table->string('input');
      $table->string('output');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('codevs_tests');
  }
};
