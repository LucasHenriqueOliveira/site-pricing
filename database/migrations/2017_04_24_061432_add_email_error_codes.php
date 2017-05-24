<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailErrorCodes extends Migration
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
        (10, 'No email in inbox'),
        (11, 'No attachment in email'),
        (12, 'Broken link in email');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE FROM `error_code` where `error_code_id` in (10, 11, 12);");
    }
}
