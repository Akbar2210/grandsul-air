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
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id(); // id_tagihan
            $table->foreignId('unit_id')->constrained('unit_hunians')->onDelete('cascade');
            $table->foreignId('bacaan_meter_id')->constrained('bacaan_meters')->onDelete('cascade');
            $table->foreignId('tarif_id')->constrained('tarif_airs')->onDelete('cascade');
            $table->string('priode'); // Format: YYYY-MM
            $table->integer('volume_pemakaian');
            $table->decimal('denda', 10, 2)->default(0);
            $table->decimal('biaya_admin', 10, 2)->default(0);
            $table->decimal('jumlah_tunggakan', 12, 2)->default(0);
            $table->decimal('total_tagihan', 12, 2);
            $table->string('status_bayar')->default('belum_lunas');
            $table->date('tgl_terbit');
            $table->date('tgl_jatuh_tempo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
