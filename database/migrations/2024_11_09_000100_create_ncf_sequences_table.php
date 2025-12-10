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
        Schema::create('ncf_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ncf_type_id');
            $table->string('serie', 20)->nullable();
            $table->unsignedBigInteger('start_number');
            $table->unsignedBigInteger('end_number');
            $table->unsignedBigInteger('current_number')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('ncf_type_id')->references('id')->on('ncf_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncf_sequences');
    }
};
