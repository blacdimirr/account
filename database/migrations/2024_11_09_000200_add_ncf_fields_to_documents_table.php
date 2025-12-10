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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('ncf_type_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('ncf_sequence_id')->nullable()->after('ncf_type_id');
            $table->string('ncf_number')->nullable()->after('ncf_sequence_id');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->unsignedBigInteger('ncf_type_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('ncf_sequence_id')->nullable()->after('ncf_type_id');
            $table->string('ncf_number')->nullable()->after('ncf_sequence_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['ncf_type_id', 'ncf_sequence_id', 'ncf_number']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['ncf_type_id', 'ncf_sequence_id', 'ncf_number']);
        });
    }
};
