<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertForRubicon2 extends Migration
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
            (48,'socialmediatoday.com'),
            (59,'theactivetimes.com'),
            (61,'thedailymeal.com');");

        DB::statement("INSERT INTO `line_item` (`line_item_id`, `publisher_id`, `source_id`, `line_item`, `device`, `geo`, `product_type_id`, `slot`, `ad_size`, `date_added`, `date_modified`)
        VALUES
        (1294,148,1,'0148-scrapdigest.com-rubicon-mob-ad-d-us','mob','us',5,'mob-box-ad-d','',NULL,NULL),
        (1295,149,1,'0149-pasionfutbol.com-rubicon_headerbidder-dsk-ad-a','dsk','us',5,'dsk-box-ad-a','',NULL,NULL),
        (1296,149,1,'0149-pasionfutbol.com-rubicon_headerbidder-dsk-ad-b','dsk','us',5,'dsk-box-ad-b','',NULL,NULL),
        (1297,149,1,'0149-pasionfutbol.com-rubicon_headerbidder-mob-ad-a','mob','us',5,'mob-box-ad-a','',NULL,NULL),
        (1298,149,1,'0149-pasionfutbol.com-rubicon_headerbidder-mob-ad-b','mob','us',5,'mob-box-ad-b','',NULL,NULL),
        (1299,149,1,'0149-pasionfutbol.com-rubicon_headerbidder-mob-ad-c','mob','us',5,'mob-box-ad-c','',NULL,NULL),
        (1300,149,1,'0149-pasionfutbol.com-rubicon-dsk-banner-ad-a-in','dsk','in',5,'dsk-banner-ad-a','',NULL,NULL),
        (1301,149,1,'0149-pasionfutbol.com-rubicon-dsk-banner-ad-a-us','dsk','us',5,'dsk-banner-ad-a','',NULL,NULL),
        (1302,149,1,'0149-pasionfutbol.com-rubicon-dsk-banner-ad-b-in','dsk','in',5,'dsk-banner-ad-b','',NULL,NULL),
        (1303,149,1,'0149-pasionfutbol.com-rubicon-dsk-box-ad-a-in','dsk','in',5,'dsk-box-ad-a','',NULL,NULL),
        (1304,149,1,'0149-pasionfutbol.com-rubicon-dsk-box-ad-a-us','dsk','us',5,'dsk-box-ad-a','',NULL,NULL),
        (1305,149,1,'0149-pasionfutbol.com-rubicon-dsk-box-ad-b-in','dsk','in',5,'dsk-box-ad-b','',NULL,NULL),
        (1306,149,1,'0149-pasionfutbol.com-rubicon-dsk-box-ad-b-us','dsk','us',5,'dsk-box-ad-b','',NULL,NULL),
        (1307,149,1,'0149-pasionfutbol.com-rubicon-dsk-box-ad-c-in','dsk','in',5,'dsk-box-ad-c','',NULL,NULL),
        (1308,149,1,'0149-pasionfutbol.com-rubicon-mob-banner-ad-a-in','mob','in',5,'mob-banner-ad-a','',NULL,NULL),
        (1309,149,1,'0149-pasionfutbol.com-rubicon-mob-banner-ad-a-us','mob','us',5,'mob-banner-ad-a','',NULL,NULL),
        (1310,149,1,'0149-pasionfutbol.com-rubicon-mob-box-ad-a-in','mob','in',5,'mob-box-ad-a','',NULL,NULL),
        (1311,149,1,'0149-pasionfutbol.com-rubicon-mob-box-ad-a-us','mob','us',5,'mob-box-ad-a','',NULL,NULL),
        (1312,149,1,'0149-pasionfutbol.com-rubicon-mob-box-ad-b-in','mob','in',5,'mob-box-ad-b','',NULL,NULL),
        (1313,149,1,'0149-pasionfutbol.com-rubicon-mob-box-ad-b-us','mob','us',5,'mob-box-ad-b','',NULL,NULL),
        (1314,149,1,'0149-pasionfutbol.com-rubicon-mob-box-ad-c-in','mob','in',5,'mob-box-ad-c','',NULL,NULL),
        (1315,149,1,'0149-pasionfutbol.com-rubicon-mob-box-ad-c-us','mob','us',5,'mob-box-ad-c','',NULL,NULL),
        (1316,151,1,'0151-mixedmartialarts.app-rubicon_headerbidder-app-box-ad-a','dsk','us',5,'mob-box-ad-a','',NULL,NULL),
        (1317,151,1,'0151-mixedmartialarts.app-rubicon-app-banner-ad-a-in','dsk','in',5,'mob-box-ad-a','',NULL,NULL),
        (1318,151,1,'0151-mixedmartialarts.app-rubicon-app-banner-ad-a-us','dsk','us',5,'mob-box-ad-a','',NULL,NULL),
        (1319,152,1,'0152-wtf1.co.uk-rubicon_headerbidder-dsk-ad-b','dsk','us',5,'dsk-box-ad-b','',NULL,NULL),
        (1320,152,1,'0152-wtf1.co.uk-rubicon_headerbidder-mob-ad-b','mob','us',5,'mob-box-ad-b','',NULL,NULL),
        (1321,152,1,'0152-wtf1.co.uk-rubicon-dsk-box-ad-a-us','dsk','us',5,'dsk-box-ad-a','',NULL,NULL),
        (1322,152,1,'0152-wtf1.co.uk-rubicon-dsk-box-ad-b-in','dsk','in',5,'dsk-box-ad-b','',NULL,NULL),
        (1323,152,1,'0152-wtf1.co.uk-rubicon-dsk-box-ad-b-us','dsk','us',5,'dsk-box-ad-b','',NULL,NULL),
        (1324,152,1,'0152-wtf1.co.uk-rubicon-dsk-box-ad-d-in','dsk','in',5,'dsk-box-ad-d','',NULL,NULL),
        (1325,152,1,'0152-wtf1.co.uk-rubicon-mob-box-ad-a-in','mob','in',5,'mob-box-ad-a','',NULL,NULL),
        (1326,152,1,'0152-wtf1.co.uk-rubicon-mob-box-ad-a-us','mob','us',5,'mob-box-ad-a','',NULL,NULL),
        (1327,152,1,'0152-wtf1.co.uk-rubicon-mob-box-ad-b-in','mob','in',5,'mob-box-ad-b','',NULL,NULL),
        (1328,152,1,'0152-wtf1.co.uk-rubicon-mob-box-ad-b-us','mob','us',5,'mob-box-ad-b','',NULL,NULL),
        (1329,156,1,'0156-vrsource.com-rubicon-dsk-banner-ad-a-in','dsk','in',5,'dsk-banner-ad-a','',NULL,NULL),
        (1330,156,1,'0156-vrsource.com-rubicon-dsk-banner-ad-a-us','dsk','us',5,'dsk-banner-ad-a','',NULL,NULL),
        (1331,156,1,'0156-vrsource.com-rubicon-dsk-box-ad-a-in','dsk','in',5,'dsk-box-ad-a','',NULL,NULL),
        (1332,156,1,'0156-vrsource.com-rubicon-dsk-box-ad-a-us','dsk','us',5,'dsk-box-ad-a','',NULL,NULL),
        (1333,156,1,'0156-vrsource.com-rubicon-dsk-box-ad-b-in','dsk','in',5,'dsk-box-ad-b','',NULL,NULL),
        (1334,156,1,'0156-vrsource.com-rubicon-dsk-box-ad-b-us','dsk','us',5,'dsk-box-ad-b','',NULL,NULL),
        (1335,156,1,'0156-vrsource.com-rubicon-mob-banner-ad-a-in','mob','in',5,'mob-banner-ad-a','',NULL,NULL),
        (1336,156,1,'0156-vrsource.com-rubicon-mob-banner-ad-a-us','mob','us',5,'mob-banner-ad-a','',NULL,NULL),
        (1337,156,1,'0156-vrsource.com-rubicon-mob-box-ad-a-in','mob','in',5,'mob-box-ad-a','',NULL,NULL),
        (1338,156,1,'0156-vrsource.com-rubicon-mob-box-ad-a-us','mob','us',5,'mob-box-ad-a','',NULL,NULL),
        (1339,156,1,'0156-vrsource.com-rubicon-mob-box-ad-b-in','mob','in',5,'mob-box-ad-b','',NULL,NULL),
        (1340,156,1,'0156-vrsource.com-rubicon-mob-box-ad-b-us','mob','us',5,'mob-box-ad-b','',NULL,NULL),
        (1341,157,1,'0157-charged.io-rubicon_headerbidder-mob-ad-b','mob','us',5,'mob-box-ad-b','',NULL,NULL),
        (1342,63,1,'entimports-us-dsk-ad-d','dsk','us',5,'dsk-box-ad-d','',NULL,NULL);
        ");

        $date = date("Y-m-d H:i:s");

        DB::statement("INSERT INTO `revenue_share` (`publisher_id`, `client_fraction`, `date_added`)
        VALUES
        (48, 0.8, '$date'),
        (59, 0.8, '$date'),
        (61, 0.8, '$date');");
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
