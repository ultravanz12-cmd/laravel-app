<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sheets', function (Blueprint $table) {
            $table->text('data')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sheets', function (Blueprint $table) {
            $table->text('data')->nullable(false)->change();
        });
    }
};