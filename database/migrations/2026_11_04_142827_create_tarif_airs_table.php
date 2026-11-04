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
        Schema::create('tarif_airs', function (Blueprint $table) {
            $table->id(); // id_tarif
            $table->string('blok_konsumsi'); // Contoh: 0-10 m3
            $table->integer('batas_bawah');
            $table->integer('batas_atas')->nullable();
            $table->decimal('tarif_per_m3', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_airs');
    }
};
