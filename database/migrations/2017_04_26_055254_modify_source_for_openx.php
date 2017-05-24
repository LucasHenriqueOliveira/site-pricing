<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySourceForOpenx extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE `source` SET `type`='Email',`line_item_field`='Site Name',
            `device_field`='Device Category', `ad_size_field`='Ad Unit Size', 
            `impressions_field`='Paid Impressions', `gross_revenue_field`='Revenue' 
            WHERE `source_id`=17;");
        DB::statement("UPDATE `source` SET `type`='Email', `publisher_name_field`='Site Name',
            `line_item_field`='Ad Unit', `device_field`='Device Category', `ad_size_field`='Ad Unit Size', 
            `impressions_field`='Paid Impressions', `gross_revenue_field`='Publisher Revenue'
            WHERE `source_id`=18;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
