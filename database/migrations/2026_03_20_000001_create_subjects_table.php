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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->foreignId('course_id')
                ->nullable()
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->nullOnDelete();

            $table->foreignId('period_id')
                ->nullable()
                ->constrained('periods')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
