<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Motor\Core\Traits\CheckForeignKeys;

return new class extends Migration
{
    use CheckForeignKeys;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('category_file')) {
            Schema::table('category_file', function (Blueprint $table) {

                if (! $this->getForeignKeyByColumns('category_file', ['category_id'])) {
                    $table->foreign(['category_id'])->references(['id'])->on('categories')->onUpdate('no action')->onDelete('cascade');
                }

                if (! $this->getForeignKeyByColumns('category_file', ['file_id'])) {
                    $table->foreign(['file_id'])->references(['id'])->on('files')->onUpdate('no action')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('category_file')) {
            Schema::table('category_file', function (Blueprint $table) {
                $table->dropForeign('category_file_category_id_foreign');
                $table->dropForeign('category_file_file_id_foreign');
            });
        }
    }
};
