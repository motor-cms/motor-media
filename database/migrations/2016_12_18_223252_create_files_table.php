<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateFilesTable
 */
class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned()->nullable()->index();
            $table->text('description');
            $table->string('author');
            $table->string('source');
            $table->string('alt_text');
            $table->boolean('is_global');

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });

        Schema::create('category_file', function (Blueprint $table) {
            $table->bigInteger('category_id')->unsigned()->index();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->bigInteger('file_id')->unsigned()->index();
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
        });

        Schema::create('file_associations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('file_id')->unsigned()->index();
            $table->morphs('model');
            $table->string('identifier');
            $table->text('custom_properties');
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('file_associations');
        Schema::drop('category_file');
        Schema::drop('files');
    }
}
