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
        Schema::create('file_associations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('file_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('identifier');
            $table->text('custom_properties');
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_associations');
    }
};
