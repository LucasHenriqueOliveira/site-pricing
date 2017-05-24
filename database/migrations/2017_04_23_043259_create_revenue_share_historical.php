<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevenueShareHistorical extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS `revenue_share_historical` (
              `revenue_share_historical_id` INT NOT NULL AUTO_INCREMENT,
              `publisher_id` INT NOT NULL,
              `client_fraction_old` DOUBLE NULL,
              `client_fraction_new` DOUBLE NULL,
              `date_added` DATETIME NULL,
              `user_id` int(11) NULL,
              PRIMARY KEY (`revenue_share_historical_id`),
              INDEX `fk_revenue_share_historical_idx` (`publisher_id` ASC),
              CONSTRAINT `fk_revenue_share_historical_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");
        DB::statement("INSERT INTO `revenue_share_historical` (publisher_id, client_fraction_new, date_added)
          SELECT publisher_id, client_fraction, '2016-01-01 00:00:00'
          FROM `revenue_share`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('revenue_share_historical');
    }
}
