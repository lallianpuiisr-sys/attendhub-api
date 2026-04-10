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
        Schema::table('semesters', function (Blueprint $table) {
            $table->decimal('geofence_latitude', 10, 7)->nullable()->after('static_qr_token');
            $table->decimal('geofence_longitude', 10, 7)->nullable()->after('geofence_latitude');
            $table->unsignedInteger('geofence_radius_meters')->nullable()->after('geofence_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn([
                'geofence_latitude',
                'geofence_longitude',
                'geofence_radius_meters',
            ]);
        });
    }
};
