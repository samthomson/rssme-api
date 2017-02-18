<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
         {
             Schema::create('tasks', function (Blueprint $table) {
                 $table->increments('id');
                 $table->string('job');
                 $table->string('name');
                 $table->string('detail');
                 $table->dateTime('processFrom');
             });
         }

         /**
          * Reverse the migrations.
          *
          * @return void
          */
         public function down()
         {
             Schema::drop('tasks');
         }
}
