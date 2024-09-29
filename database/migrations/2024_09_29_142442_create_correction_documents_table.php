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
        Schema::create('spelling_corrections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('user_id')->index();
            $table->string('name');
            $table->string('result')->nullable();
            $table->string('status');
            $table->enum('type', ['testing', 'training']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spelling_corrections');
    }
};
