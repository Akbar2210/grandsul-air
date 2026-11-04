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
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id(); // id_notifikasi
            $table->foreignId('unit_id')->constrained('unit_hunians')->onDelete('cascade');
            $table->text('pesan');
            $table->dateTime('tgl_kirim');
            $table->boolean('status_terkirim')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
