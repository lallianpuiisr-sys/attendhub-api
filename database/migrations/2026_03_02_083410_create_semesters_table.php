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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();

            // relation to courses table
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->string('title'); // e.g. "Semester 1", "Semester 2"
            $table->text('description')->nullable();

            $table->integer('semester_number'); // 1, 2, 3, 4 etc
            $table->string('static_qr_token')->nullable()->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
