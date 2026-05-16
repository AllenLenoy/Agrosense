<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->onDelete('cascade');
            $table->decimal('soil_moisture', 5, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('water_level', 5, 2)->nullable();
            $table->decimal('light_intensity', 8, 2)->nullable();
            $table->decimal('ph_level', 4, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['sensor_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
