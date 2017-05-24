<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailErrorCodes2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `error_code` (`error_code_id`, `name`)
        VALUES
        (13, 'Problem with csv attachment'),
        (14, 'Problem with xslx attachment');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         DB::statement("DELETE FROM `error_code` where `error_code_id` in (13, 14);");
    }
}
