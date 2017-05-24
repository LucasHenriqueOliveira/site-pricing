<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS `product_type` (
              `product_type_id` INT NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`product_type_id`));
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `publisher` (
              `publisher_id` INT NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`publisher_id`));
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `email` varchar(100) NOT NULL,
              `password` text NOT NULL,
              `remember_token` varchar(100) DEFAULT NULL,
              `is_superuser` tinyint(1) NOT NULL DEFAULT '0',
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB;
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `user_publisher` (
              `user_id` INT NOT NULL,
              `publisher_id` INT NOT NULL,
              INDEX `fk_users_idx` (`user_id` ASC),
              INDEX `fk_publisher_idx` (`publisher_id` ASC),
              CONSTRAINT `fk_users`
                FOREIGN KEY (`user_id`)
                REFERENCES `users` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `fk_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");

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

        DB::statement("
            CREATE TABLE IF NOT EXISTS `source` (
              `source_id` INT NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NULL,
              `ingestor_class` VARCHAR(255) NULL,
              `processor_class` VARCHAR(255) NULL,
              `type` ENUM('API', 'Email', 'Page') NULL,
              `page_views_field` VARCHAR(100) NULL,
              `impressions_field` VARCHAR(100) NULL,
              `gross_revenue_field` VARCHAR(100) NULL,
              `num_clicks_field` VARCHAR(100) NULL,
              `viewable_field` VARCHAR(100) NULL,
              `device_field` VARCHAR(100) NULL,
              `line_item_field` VARCHAR(100) NULL,
              `geo_field` VARCHAR(100) NULL,
              `product_field` VARCHAR(100) NULL,
              `slot_field` VARCHAR(100) NULL,
              `ad_size_field` VARCHAR(100) NULL,
              `date_added` DATETIME NULL,
              `date_modified` DATETIME NULL,
              PRIMARY KEY (`source_id`));
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `source_status` (
              `source_status_id` INT NOT NULL AUTO_INCREMENT,
              `source_id` INT NOT NULL,
              `status` ENUM('Success', 'Client Error', 'Server Error') NULL,
              `date` DATETIME NULL,
              PRIMARY KEY (`source_status_id`),
              INDEX `fk_source_status_idx` (`source_id` ASC),
              CONSTRAINT `fk_source`
                FOREIGN KEY (`source_id`)
                REFERENCES `source` (`source_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `cron` (
              `cron_id` INT NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(45) NULL,
              `class` VARCHAR(255) NULL,
              `start_date` DATETIME NULL,
              `interval` ENUM('minute', 'hour', 'day', 'week') NULL DEFAULT 'day',
              `interval_unity` TINYINT(2) NULL DEFAULT 1,
              `next_time` DATETIME NULL,
              `finished` DATETIME NULL,
              `interactions` INT NULL DEFAULT 0,
              PRIMARY KEY (`cron_id`));
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `cron_status` (
              `cron_status_id` INT NOT NULL AUTO_INCREMENT,
              `cron_id` INT NOT NULL,
              `status` ENUM('idle', 'running') NULL,
              `date` DATETIME NULL,
              PRIMARY KEY (`cron_status_id`),
              INDEX `fk_cron_idx` (`cron_id` ASC),
              CONSTRAINT `fk_cron`
                FOREIGN KEY (`cron_id`)
                REFERENCES `cron` (`cron_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `metric_by_source_and_full_split_daily` (
              `metric_by_source_and_full_split_daily_id` INT NOT NULL AUTO_INCREMENT,
              `date` DATETIME NULL,
              `publisher_id` INT NOT NULL,
              `source_id` INT NOT NULL,
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
              PRIMARY KEY (`metric_by_source_and_full_split_daily_id`),
              INDEX `fk_source_full_split_idx` (`publisher_id` ASC),
              INDEX `fk_source_full_split_idx1` (`source_id` ASC),
              UNIQUE INDEX `date_UNIQUE` (`date`,`publisher_id`,`source_id`,`device`,`geo`,`product_type_id`,`slot`,`ad_size`),
              CONSTRAINT `fk_source_full_split_product_type`
                FOREIGN KEY (`product_type_id`)
                REFERENCES `product_type` (`product_type_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `fk_source_full_split_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `fk_source_full_split`
                FOREIGN KEY (`source_id`)
                REFERENCES `source` (`source_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `revenue_share` (
              `revenue_share_id` INT NOT NULL AUTO_INCREMENT,
              `publisher_id` INT NOT NULL,
              `client_fraction` DOUBLE NULL,
              `date_added` DATETIME NULL,
              `date_modified` DATETIME NULL,
              PRIMARY KEY (`revenue_share_id`),
              INDEX `fk_revenue_share_idx` (`publisher_id` ASC),
              CONSTRAINT `fk_revenue_share_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `source_publisher_id_map` (
              `source_id` INT NOT NULL,
              `publisher_id` INT NOT NULL,
              `source_publisher_id` VARCHAR(255) NOT NULL,
              `date_added` DATETIME NULL,
              `date_modified` DATETIME NULL,
              INDEX `fk_source_publisher_id_map_source_idx` (`source_id` ASC),
              INDEX `fk_source_publisher_id_map_publisher_idx` (`publisher_id` ASC),
              CONSTRAINT `fk_source_publisher_id_map_source`
                FOREIGN KEY (`source_id`)
                REFERENCES `source` (`source_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `fk_source_publisher_id_map_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `line_item` (
              `line_item_id` INT NOT NULL AUTO_INCREMENT,
              `publisher_id` INT NOT NULL,
              `source_id` INT NOT NULL,
              `line_item` VARCHAR(255) NULL,
              `device` VARCHAR(100) NULL,
              `geo` VARCHAR(45) NULL,
              `product_type_id` INT NOT NULL,
              `slot` VARCHAR(255) NULL,
              `ad_size` VARCHAR(255) NULL,
              `date_added` DATETIME NULL,
              `date_modified` DATETIME NULL,
              PRIMARY KEY (`line_item_id`),
              INDEX `fk_source_full_split_idx` (`publisher_id` ASC),
              INDEX `fk_source_full_split_idx1` (`source_id` ASC),
              CONSTRAINT `fk_line_item_product_type`
                FOREIGN KEY (`product_type_id`)
                REFERENCES `product_type` (`product_type_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `fk_line_item_publisher`
                FOREIGN KEY (`publisher_id`)
                REFERENCES `publisher` (`publisher_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `fk_line_item_source`
                FOREIGN KEY (`source_id`)
                REFERENCES `source` (`source_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('metric_by_source_and_full_split_daily');
        Schema::drop('metric_daily');
        Schema::drop('source_publisher_id_map');
        Schema::drop('source_status');
        Schema::drop('user_publisher');
        Schema::drop('line_item');
        Schema::drop('revenue_share');
        Schema::drop('product_type');
        Schema::drop('source');
        Schema::drop('publisher');
        Schema::drop('cron');
        Schema::drop('cron_status');
    }
}
