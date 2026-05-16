<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->time('start_time');
            $table->integer('duration_minutes');
            $table->json('days_of_week');
            $table->decimal('moisture_threshold', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_smart')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_schedules');
    }
};
