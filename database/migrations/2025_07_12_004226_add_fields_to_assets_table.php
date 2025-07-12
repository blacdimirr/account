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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('area')->nullable();
            $table->string('code_active')->nullable();
            $table->string('code_active_category')->nullable();
            $table->date('date_garantia')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();

            $table->foreign('proveedor_id')->references('vender_id')->on('Vender')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            //
        });
    }
};
