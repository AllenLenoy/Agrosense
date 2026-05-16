<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('crop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('image_path');
            $table->string('disease_name')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->text('description')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prevention')->nullable();
            $table->enum('status', ['pending', 'analyzed', 'treated', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_reports');
    }
};
