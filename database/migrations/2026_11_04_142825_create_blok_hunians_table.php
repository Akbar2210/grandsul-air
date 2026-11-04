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
       Schema::create('blok_hunians', function (Blueprint $table) {
            $table->id(); // id_blok
            $table->string('nama_blok'); // Contoh: Blok A, Blok B
            $table->string('keterangan_wilayah')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blok_hunians');
    }
};
