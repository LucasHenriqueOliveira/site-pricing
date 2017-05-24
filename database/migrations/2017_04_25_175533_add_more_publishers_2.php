<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMorePublishers2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `publisher` (`publisher_id`, `name`, `site_name`, `site_code`, `active`)
        VALUES
            (184,'johnbiver.com', 'John Biver', 'johnbiver.com', 1),
            (185,'bloodlettersandbadmen.com', 'Bloodletters & Badmen', 'bloodlettersandbadmen.com', 1),
            (186,'opslens.com', 'OpsLens', 'opslens.com', 1),
            (187,'gayot.com', 'Gayot', 'gayot.com', 1),
            (188,'thepolitistick.com', 'The PolitiStick', 'thepolitistick.com', 1),
            (189,'upstater.com', 'upstater', 'upstater.com', 1);");
        DB::statement("INSERT INTO `revenue_share` (`publisher_id`, `client_fraction`, `date_added`, `date_modified`)
        VALUES
            (184,0.8,'2017-04-24 04:29:17',NULL),
            (185,0.8,'2017-04-24 04:29:17',NULL),
            (186,0.8,'2017-04-24 04:29:17',NULL),
            (187,0.8,'2017-04-24 04:29:17',NULL),
            (188,0.8,'2017-04-24 04:29:17',NULL),
            (189,0.8,'2017-04-24 04:29:17',NULL);");

        DB::statement("INSERT INTO `revenue_share_historical` (`publisher_id`, `client_fraction_new`, `date_added`)
        VALUES
            (184,0.8,'2016-01-01 00:00:00'),
            (185,0.8,'2016-01-01 00:00:00'),
            (186,0.8,'2016-01-01 00:00:00'),
            (187,0.8,'2016-01-01 00:00:00'),
            (188,0.8,'2016-01-01 00:00:00'),
            (189,0.8,'2016-01-01 00:00:00');");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE FROM `revenue_share_historical` where `publisher_id` in (184, 185, 186, 187, 188, 189);");
        DB::statement("DELETE FROM `revenue_share` where `publisher_id` in (184, 185, 186, 187, 188, 189);");
        DB::statement("DELETE FROM `publisher` where `publisher_id` in (184, 185, 186, 187, 188, 189);");
    }
}
