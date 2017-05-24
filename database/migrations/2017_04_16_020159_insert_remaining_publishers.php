<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// This is actually the remaining sources not publishers
class InsertRemainingPublishers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("INSERT INTO `source` (`source_id`, `name`, `ingestor_class`, `processor_class`, `type`, `page_views_field`, `impressions_field`, `gross_revenue_field`, `num_clicks_field`, `viewable_field`, `device_field`, `line_item_field`, `geo_field`, `product_field`, `slot_field`, `ad_size_field`, `date_added`, `date_modified`)
            VALUES
                (12,'Swoop','SwoopController',NULL,'native',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (15,'AdX','AdXController',NULL,'display',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (16,'AdXVideo','AdXController',NULL,'video',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (17,'OpenX','OpenXController',NULL,'display',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (18,'OpenXBidder','OpenXController',NULL,'display',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (19,'AppNexus','AppNexusController',NULL,'display',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (21,'Sharethrough','SharethroughController',NULL,'native',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (22,'Mediabong','SheetController',NULL,'native',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (24,'Undertone','SheetController',NULL,'display',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (25,'UndertoneVideo','SheetController',NULL,'video',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
                (26,'Tremor','SheetController',NULL,'video',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);");
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
