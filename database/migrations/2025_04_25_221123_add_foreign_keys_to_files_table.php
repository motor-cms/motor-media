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
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {

                if (! $this->getForeignKeyByColumns('files', ['client_id'])) {
                    $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('set null');
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropForeign('files_client_id_foreign');
            });
        }
    }
};
