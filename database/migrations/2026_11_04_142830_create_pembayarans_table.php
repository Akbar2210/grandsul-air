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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id(); // id_pembayaran
            $table->foreignId('tagihan_id')->constrained('tagihans')->onDelete('cascade');
            $table->string('bukti_bayar')->nullable();
            $table->decimal('jumlah_bayar', 12, 2);
            $table->string('metode_bayar'); // Cash / Transfer / QRIS
            $table->dateTime('tgl_bayar');
            $table->string('status_pembayaran'); // Sukses / Pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
