<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePublisherDupont extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE `publisher` SET `name`='blog.dupontregistry.com' WHERE `publisher_id`=5;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE `publisher` SET `name`='blog.dupontregistry' WHERE `publisher_id`=5;");
    }
}
