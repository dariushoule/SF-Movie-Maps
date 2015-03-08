<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialSchema extends Migration {

	/**
	 * Create the title and actor tables.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('title', function($table)
        {
            $table->increments('title_id');
            $table->string('name', 200)->index();
            $table->string('producer', 100);
            $table->string('distributor', 100);
            $table->string('writer', 100);
            $table->string('director', 100);
            $table->string('image_url', 100);
            $table->text('fun_facts');
            $table->date('year');
            $table->integer('checksum')->unsigned();
            $table->timestamps();
        });

        Schema::create('actor', function($table)
        {
            $table->increments('actor_id');
            $table->string('first', 100);
            $table->string('last', 100);
        });

        Schema::create('location', function($table)
        {
            $table->increments('location_id');
            $table->string('description', 100);
            $table->decimal('lat', 12, 8);
            $table->decimal('lng', 12, 8);
        });

        Schema::create('title_actor', function($table)
        {
            $table->integer('title_id')->unsigned();
            $table->foreign('title_id')->references('title_id')->on('title');
            $table->integer('actor_id')->unsigned();
            $table->foreign('actor_id')->references('actor_id')->on('actor');
        });

        Schema::create('title_location', function($table)
        {
            $table->integer('title_id')->unsigned();
            $table->foreign('title_id')->references('title_id')->on('title');
            $table->integer('location_id')->unsigned();
            $table->foreign('location_id')->references('location_id')->on('location');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('title_location');
        Schema::dropIfExists('title_actor');
        Schema::dropIfExists('title');
        Schema::dropIfExists('location');
        Schema::dropIfExists('actor');
	}

}
