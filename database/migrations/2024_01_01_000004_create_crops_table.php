<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('variety')->nullable();
            $table->date('planted_at')->nullable();
            $table->date('expected_harvest')->nullable();
            $table->date('harvested_at')->nullable();
            $table->enum('status', ['seedling', 'growing', 'mature', 'harvesting', 'harvested'])->default('seedling');
            $table->integer('health_score')->default(100);
            $table->decimal('expected_yield_kg', 10, 2)->nullable();
            $table->decimal('actual_yield_kg', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
