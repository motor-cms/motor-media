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
        Schema::table('category_file', function (Blueprint $table) {
            $table->foreign(['category_id'])->references(['id'])->on('categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['file_id'])->references(['id'])->on('files')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_file', function (Blueprint $table) {
            $table->dropForeign('category_file_category_id_foreign');
            $table->dropForeign('category_file_file_id_foreign');
        });
    }
};
