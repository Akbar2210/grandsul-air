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
        Schema::create('unit_hunians', function (Blueprint $table) {
            $table->id(); // id_unit
            $table->foreignId('blok_id')->constrained('blok_hunians')->onDelete('cascade');
            $table->string('nomor_rumah');
            $table->string('nama_pemilik');
            $table->string('nomor_wa');
            $table->string('status_huni'); // Dihuni / Kosong
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_hunians');
    }
};
