<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertForTaboola extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    DB::statement("INSERT INTO `source` (`source_id`, `name`, `ingestor_class`, `processor_class`, `type`, `page_views_field`, `impressions_field`, `gross_revenue_field`, `num_clicks_field`, `viewable_field`, `device_field`, `line_item_field`, `geo_field`, `product_field`, `slot_field`, `ad_size_field`, `date_added`, `date_modified`)
VALUES
    (5,'Taboola',NULL,NULL,'Page',NULL,'View with Ads','Ad Revenue',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::delete('delete from line_item where source_id = 5;');
        DB::delete('delete from source where source_id = 5;');
    }
}
