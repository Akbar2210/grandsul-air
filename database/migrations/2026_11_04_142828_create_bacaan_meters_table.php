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
        Schema::create('bacaan_meters', function (Blueprint $table) {
            $table->id(); // id_bacaan
            $table->foreignId('unit_id')->constrained('unit_hunians')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Petugas
            $table->string('foto_meter');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('angka_meter_bulanlalu');
            $table->integer('angka_meter');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status_verifikasi')->default('valid');
            $table->float('confidence_score')->nullable();
            $table->boolean('is_ocr_corrected')->default(false);
            $table->text('catatan_anomali')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bacaan_meters');
    }
};
