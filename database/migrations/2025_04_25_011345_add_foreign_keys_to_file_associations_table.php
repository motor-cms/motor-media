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
        Schema::table('file_associations', function (Blueprint $table) {
            $table->foreign(['file_id'])->references(['id'])->on('files')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_associations', function (Blueprint $table) {
            $table->dropForeign('file_associations_file_id_foreign');
        });
    }
};
