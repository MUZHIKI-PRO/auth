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
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable();
            $table->string('yclients_id')->nullable();
            $table->string('yclients_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Вернуть password и email как NOT NULL
            $table->string('password')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();

            // Удалить добавленные поля
            $table->dropColumn(['phone', 'yclients_id', 'yclients_user_id']);
        });
    }
};
