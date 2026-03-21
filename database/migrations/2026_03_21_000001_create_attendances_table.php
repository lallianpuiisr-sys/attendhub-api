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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('attendance_session_id')
                ->constrained('attendance_sessions')
                ->cascadeOnDelete();

            $table->enum('status', ['present', 'absent', 'late']);
            $table->dateTime('scanned_at')->nullable();
            $table->string('device_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'attendance_session_id'], 'attendances_user_session_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
