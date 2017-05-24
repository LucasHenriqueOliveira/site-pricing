<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMetricsForGrossRevenue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `metric_by_full_split_daily` ADD COLUMN `gross_revenue` DOUBLE NULL AFTER `impressions`;");
        DB::statement("ALTER TABLE `metric_by_source_and_full_split_daily` ADD COLUMN `gross_revenue` DOUBLE NULL AFTER `impressions`;");
        // This is assuming that 0.8 was the fraction that was used
        DB::statement("UPDATE `metric_by_full_split_daily` set gross_revenue = (`net_revenue` / 0.8) WHERE net_revenue is not null;");
        DB::statement("UPDATE `metric_by_source_and_full_split_daily` set gross_revenue = (`net_revenue` / 0.8) WHERE net_revenue is not null;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `metric_by_full_split_daily` DROP COLUMN `gross_revenue`;");
        DB::statement("ALTER TABLE `metric_by_source_and_full_split_daily` DROP COLUMN `gross_revenue`;");
    }
}
