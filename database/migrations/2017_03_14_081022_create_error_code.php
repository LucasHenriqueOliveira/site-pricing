<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE TABLE `error_code` (
              `error_code_id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`error_code_id`)
            );
        ");

        DB::statement("INSERT INTO `error_code` (`error_code_id`, `name`)
        VALUES
        (1,'Site is down'),
        (2,'Credentials dont work'),
        (3,'Site is slow and timed out'),
        (4,'API has changed'),
        (5,'No data for any publishers for the current date is yet available'),
        (6,'Unrecognized publishers'),
        (7,'Line item cannot be parsed'),
        (8,'Incomplete data'),
        (9,'Invalid values');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('error_code');
    }
}
