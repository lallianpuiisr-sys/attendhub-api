<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff_details', function (Blueprint $table) {
            $table->id(); // id

            $table->foreignId('user_id') // user_id
                ->constrained('users')
                ->cascadeOnDelete()
                ->unique();

            $table->string('position')->nullable(); // (you wrote "possition")

            $table->boolean('is_admin')->default(false);
            $table->boolean('is_teacher')->default(false);
            $table->boolean('is_receptionist')->default(false);
            $table->boolean('is_approved')->default(false);

            $table->string('phone_1', 20)->nullable();
            $table->string('phone_2', 20)->nullable();

            // Recommended fields
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['is_admin', 'is_teacher', 'is_receptionist']);
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_details');
    }
};
