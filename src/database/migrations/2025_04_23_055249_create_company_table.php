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
        Schema::create('mpa_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('address');
            $table->unsignedBigInteger('yclients_id')->nullable();
            $table->timestamps();

            $table->index(['yclients_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
