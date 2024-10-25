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
        Schema::table('contents', function (Blueprint $table) {
            $table->unsignedBigInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->foreign('edited_by')->references('id')->on('users')->onDelete('cascade');

        });
    }


    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('added_by');
            $table->dropColumn('edited_by');
        });
    }
};
