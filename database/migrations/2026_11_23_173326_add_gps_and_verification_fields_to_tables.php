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
        Schema::table('unit_hunians', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('status_huni');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->unsignedInteger('radius_toleransi')->default(50)->after('longitude');
        });

        Schema::table('bacaan_meters', function (Blueprint $table) {
            $table->boolean('gps_unverified')->default(false)->after('status_verifikasi');
            $table->text('alasan_penolakan')->nullable()->after('catatan_anomali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_hunians', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_toleransi']);
        });

        Schema::table('bacaan_meters', function (Blueprint $table) {
            $table->dropColumn(['gps_unverified', 'alasan_penolakan']);
        });
    }
};
