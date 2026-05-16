<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('sensor_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['warning', 'critical', 'info', 'success'])->default('info');
            $table->enum('category', ['moisture', 'temperature', 'humidity', 'water_level', 'device', 'weather', 'disease', 'irrigation', 'system']);
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
