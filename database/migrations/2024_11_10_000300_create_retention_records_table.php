<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->string('retention_type');
            $table->decimal('base_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('retained_amount', 18, 2)->default(0);
            $table->decimal('rate', 8, 4)->default(0);
            $table->string('ncf_number')->nullable();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
            $table->index(['period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_records');
    }
};
