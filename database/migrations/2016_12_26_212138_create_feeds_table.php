<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('thumb')->default('');
            $table->integer('hit_count')->default(0);
            $table->integer('lastPulledCount')->default(0)->unsigned();
            $table->integer('item_count')->default(0);
            $table->boolean('failing')->default(FALSE);
            $table->dateTime('lastPulled')->nullable();
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
        Schema::dropIfExists('feeds');
    }
}
