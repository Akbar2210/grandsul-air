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
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id(); // id_signature
            $table->foreignId('tagihan_id')->constrained('tagihans')->onDelete('cascade');
            $table->text('hash_sha256');
            $table->text('digital_signature'); // RSA-2048 Encrypted Signature
            $table->string('public_key_fingerprint');
            $table->string('qr_code_path');
            $table->dateTime('tgl_signed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
