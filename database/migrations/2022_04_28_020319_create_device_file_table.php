<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Device_File', function (Blueprint $table) {
            // auto id
            $table->id();

            // device foreign key
            $table->integer('device_id')->unsigned()->index();
            $table->foreign('device_id')->references('auto_id')->on('Equipment')->onDelete('cascade');

            // file foreign key
            $table->integer('file_id')->unsigned()->index();
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');

            // settting up the unique primary key
            $table->primary(['device_id', 'file_id']);
            
            // timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_file');
    }
}
