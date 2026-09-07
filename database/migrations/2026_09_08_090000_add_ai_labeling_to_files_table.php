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
        Schema::table('files', function (Blueprint $table) {
            if (! Schema::hasColumn('files', 'ai_labeling')) {
                $table->string('ai_labeling')
                    ->nullable()
                    ->default(null)
                    ->after('alt_text');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            if (Schema::hasColumn('files', 'ai_labeling')) {
                $table->dropColumn('ai_labeling');
            }
        });
    }
};
