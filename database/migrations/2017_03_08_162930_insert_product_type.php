<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertProductType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `product_type` (`product_type_id`, `name`)
        VALUES
        (1,'contextual'),
        (2,'managed 3rd party'),
        (3,'native'),
        (4,'video'),
        (5,'display');");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::delete('delete from product_type;');
    }
}
