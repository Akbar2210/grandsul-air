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
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->string('judul')->nullable()->after('id');
            $table->string('tipe', 50)->default('info')->after('pesan');
            $table->string('link')->nullable()->after('tipe');
            $table->boolean('is_read')->default(false)->after('link');
            $table->unsignedBigInteger('unit_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropColumn(['judul', 'tipe', 'link', 'is_read']);
        });
    }
};
