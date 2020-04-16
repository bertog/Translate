<?php

namespace TheNonsenseFactory\Translate\Tests;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseSetup();

    }

    protected function databaseSetup()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lang', 2);
            $table->string('field');
            $table->string('text');
            $table->integer('translatable_id');
            $table->string('translatable_type');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });


    }

}