<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirstAndLastNamesToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `users` ADD COLUMN last_name varchar(255) NULL AFTER `name`;");
        DB::statement("ALTER TABLE `users` ADD COLUMN first_name varchar(255) NULL AFTER `name`;");

        DB::statement("UPDATE `users` SET `first_name`=substring_index(`name`, ' ', 1), `last_name`=substring_index(`name`, ' ', -1);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `users` DROP COLUMN `first_name`;");
        DB::statement("ALTER TABLE `users` DROP COLUMN `last_name`;");
    }
}
