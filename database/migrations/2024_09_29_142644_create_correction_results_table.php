<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('correction_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_id')->index();
            $table->text('incorrect_word');
            $table->text('correct_word');
            $table->text('correction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_results');
    }
};
