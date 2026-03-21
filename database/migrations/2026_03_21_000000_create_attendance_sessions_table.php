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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('period_id')
                ->constrained('periods')
                ->cascadeOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('status', ['active', 'expired'])->default('active');
            $table->string('qr_token');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique([
                'course_id',
                'semester_id',
                'subject_id',
                'period_id',
                'starts_at',
            ], 'attendance_sessions_uniq');

            $table->unique(['qr_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
