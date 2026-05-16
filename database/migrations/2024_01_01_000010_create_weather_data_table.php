<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->string('wind_direction')->nullable();
            $table->decimal('rainfall_mm', 8, 2)->nullable();
            $table->string('condition')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('uv_index', 4, 2)->nullable();
            $table->integer('visibility_km')->nullable();
            $table->decimal('pressure_hpa', 6, 1)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['farm_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
