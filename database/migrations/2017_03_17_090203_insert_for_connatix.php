<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertForConnatix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

    DB::statement("INSERT INTO `publisher` (`publisher_id`, `name`)
        VALUES
        (62,'mma.tv'),
        (30,'soundguys.com');");

        DB::statement("INSERT INTO `source` (`source_id`, `name`, `ingestor_class`, `processor_class`, `type`, `page_views_field`, `impressions_field`, `gross_revenue_field`, `num_clicks_field`, `viewable_field`, `device_field`, `line_item_field`, `geo_field`, `product_field`, `slot_field`, `ad_size_field`, `date_added`, `date_modified`)
VALUES
	(3,'Connatix',NULL,NULL,'Page',NULL,'ViewableImpressions','Earnings',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);");


    $date = date("Y-m-d h:i:s");

    DB::statement("INSERT INTO `revenue_share` (`publisher_id`, `client_fraction`, `date_added`)
    VALUES
    (62, 0.8, '$date'),
    (30, 0.8, '$date');");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::delete('delete from line_item where source_id = 3;');
        DB::delete('delete from source where source_id = 3;');
    }
}
