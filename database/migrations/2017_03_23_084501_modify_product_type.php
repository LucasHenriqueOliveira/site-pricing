<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProductType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("UPDATE `product_type` SET `name`='Contextual (rCPM)' WHERE `product_type_id`='1';");
        DB::statement("UPDATE `product_type` SET `name`='Managed 3rd party' WHERE `product_type_id`='2';");
        DB::statement("UPDATE `product_type` SET `name`='Native' WHERE `product_type_id`='3';");
        DB::statement("UPDATE `product_type` SET `name`='Video' WHERE `product_type_id`='4';");
        DB::statement("UPDATE `product_type` SET `name`='Display' WHERE `product_type_id`='5';");


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE `product_type` SET `name`='contextual' WHERE `product_type_id`='1';");
        DB::statement("UPDATE `product_type` SET `name`='managed 3rd party' WHERE `product_type_id`='2';");
        DB::statement("UPDATE `product_type` SET `name`='native' WHERE `product_type_id`='3';");
        DB::statement("UPDATE `product_type` SET `name`='video' WHERE `product_type_id`='4';");
        DB::statement("UPDATE `product_type` SET `name`='display' WHERE `product_type_id`='5';");
    }
}
