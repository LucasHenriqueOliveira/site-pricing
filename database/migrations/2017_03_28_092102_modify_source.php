<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE `source` SET `ingestor_class`='RubiconController' WHERE `source_id`='1';");
        DB::statement("UPDATE `source` SET `ingestor_class`='SovrnController' WHERE `source_id`='2';");
        DB::statement("UPDATE `source` SET `ingestor_class`='ConnatixController' WHERE `source_id`='3';");
        DB::statement("UPDATE `source` SET `ingestor_class`='TechnoratiController' WHERE `source_id`='4';");
        DB::statement("UPDATE `source` SET `ingestor_class`='TaboolaController' WHERE `source_id`='5';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE `source` SET `ingestor_class`=null WHERE `source_id`='1';");
        DB::statement("UPDATE `source` SET `ingestor_class`=null WHERE `source_id`='2';");
        DB::statement("UPDATE `source` SET `ingestor_class`=null WHERE `source_id`='3';");
        DB::statement("UPDATE `source` SET `ingestor_class`=null WHERE `source_id`='4';");
        DB::statement("UPDATE `source` SET `ingestor_class`=null WHERE `source_id`='5';");
    }
}