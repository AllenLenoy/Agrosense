<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_id')->nullable()->constrained()->onDelete('set null');
            $table->string('device_id')->unique();
            $table->string('name');
            $table->enum('type', ['soil_moisture', 'temperature', 'humidity', 'water_level', 'light_intensity', 'ph', 'multi']);
            $table->enum('status', ['online', 'offline', 'maintenance', 'error'])->default('offline');
            $table->string('model')->nullable();
            $table->string('firmware_version')->nullable();
            $table->decimal('battery_level', 5, 2)->nullable();
            $table->timestamp('last_reading_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};
