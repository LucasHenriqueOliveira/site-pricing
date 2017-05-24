<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchema2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS `metric_by_full_split_daily` (
            `metric_by_full_split_daily_id` INT NOT NULL AUTO_INCREMENT,
            `date` DATETIME NULL,
            `publisher_id` INT NOT NULL,
            `device` VARCHAR(100) NULL,
            `geo` VARCHAR(45) NULL,
            `product_type_id` INT NOT NULL,
            `slot` VARCHAR(255) NULL,
            `ad_size` VARCHAR(255) NULL,
            `page_views` INT NULL,
            `impressions` INT NULL,
            `net_revenue` DOUBLE NULL,
            `ecpm` DOUBLE NULL,
            `rpm` DOUBLE NULL,
            `ctr` DOUBLE NULL,
            `viewability` DOUBLE NULL,
            PRIMARY KEY (`metric_by_full_split_daily_id`),
            INDEX `fk_full_split_idx` (`publisher_id` ASC),
            UNIQUE INDEX `date_UNIQUE` (`date`,`publisher_id`,`device`,`geo`,`product_type_id`,`slot`,`ad_size`),
            CONSTRAINT `fk_full_split_publisher_product_type`
                FOREIGN KEY (`product_type_id`)
                REFERENCES `product_type` (`product_type_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
            CONSTRAINT `fk_full_split_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");
        Schema::drop('metric_daily');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('metric_by_full_split_daily');
        DB::statement("
            CREATE TABLE IF NOT EXISTS `metric_daily` (
              `metric_daily_id` INT NOT NULL AUTO_INCREMENT,
              `date` DATETIME NULL,
              `publisher_id` INT NOT NULL,
              `page_views` INT NULL,
              `impressions` INT NULL,
              `net_revenue` DOUBLE NULL,
              `ecpm` DOUBLE NULL,
              `rpm` DOUBLE NULL,
              `ctr` DOUBLE NULL,
              `viewability` DOUBLE NULL,
              PRIMARY KEY (`metric_daily_id`),
              INDEX `fk_metric_publisher_idx` (`publisher_id` ASC),
              UNIQUE INDEX `date_UNIQUE` (`date`,`publisher_id`),
              CONSTRAINT `fk_metric_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");
    }
}
