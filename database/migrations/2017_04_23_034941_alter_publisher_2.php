<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPublisher2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `publisher` (`publisher_id`, `name`, `active`)
        VALUES(180,'thinkamericana.com', 1),
              (181,'knappyearseve2016.com', 1),
              (182,'conservativereview.com', 1),
              (183,'pokercentral.com', 1);");
        DB::statement("INSERT INTO `revenue_share` (`revenue_share_id`, `publisher_id`, `client_fraction`, `date_added`, `date_modified`)
            VALUES
                (177,180,0.8,'2017-04-20 04:29:17',NULL),
                (178,181,0.8,'2017-04-20 04:29:17',NULL),
                (179,182,0.8,'2017-04-20 04:29:17',NULL),
                (180,183,0.8,'2017-04-20 04:29:17',NULL);");
        DB::statement("ALTER TABLE `publisher` ADD COLUMN site_tb varchar(255) NULL AFTER `name`;");
        DB::statement("ALTER TABLE `publisher` ADD COLUMN site_ga varchar(50) NULL AFTER `name`;");
        DB::statement("ALTER TABLE `publisher` ADD COLUMN site_code varchar(255) NOT NULL AFTER `name`;");
        DB::statement("ALTER TABLE `publisher` ADD COLUMN site_name varchar(255) NOT NULL AFTER `name`;");
        DB::statement('UPDATE `publisher` SET `site_name` = "TechnoBuffalo", `site_code` = "technobuffalo.com", `site_tb` = "thepublisherdesk-androidauthority" WHERE publisher_id = 1;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Android Authority", `site_code` = "androidauthority.com", `site_ga` = "UA-41929724-2", `site_tb` = "thepublisherdesk-askdrmanny" WHERE publisher_id = 2;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Ask Dr. Manny", `site_code` = "askdrmanny.com" WHERE publisher_id = 3;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BIOS Sleep System", `site_code` = "biossleepsystem.com", `site_tb` = "thepublisherdesk-dupontregistry" WHERE publisher_id = 4;');
        DB::statement('UPDATE `publisher` SET `site_name` = "duPont Registry - Autos Blog", `site_code` = "blog.dupontregistry.com", `site_ga` = "UA-41929724-10" WHERE publisher_id = 5;');
        DB::statement('UPDATE `publisher` SET `site_name` = "CainTV", `site_code` = "caintv.com" WHERE publisher_id = 6;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Confitdent", `site_code` = "confitdent.com", `site_ga` = "UA-41929724-7" WHERE publisher_id = 7;');
        DB::statement('UPDATE `publisher` SET `site_name` = "DailyNewsPo", `site_code` = "dailynewspo.com" WHERE publisher_id = 8;');
        DB::statement('UPDATE `publisher` SET `site_name` = "dLife", `site_code` = "dlife.com" WHERE publisher_id = 9;');
        DB::statement('UPDATE `publisher` SET `site_name` = "duPont Registry - Autos", `site_code` = "dupontregistry.com.autos", `site_ga` = "UA-41929724-9" WHERE publisher_id = 10;');
        DB::statement('UPDATE `publisher` SET `site_name` = "duPont Registry - Boats", `site_code` = "dupontregistry.com.boats", `site_ga` = "UA-41929724-9" WHERE publisher_id = 11;');
        DB::statement('UPDATE `publisher` SET `site_name` = "duPont Registry - Homes", `site_code` = "dupontregistry.com.homes", `site_ga` = "UA-41929724-9" WHERE publisher_id = 12;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Healthspo", `site_code` = "healthspo.com" WHERE publisher_id = 13;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Maxim", `site_code` = "maxim.com" WHERE publisher_id = 14;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Sodahead", `site_code` = "sodahead.com" WHERE publisher_id = 15;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ThrifyCity", `site_code` = "thriftycity.com" WHERE publisher_id = 16;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Veria", `site_code` = "veria.com" WHERE publisher_id = 17;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Young Married Chic", `site_code` = "youngmarriedchic.com" WHERE publisher_id = 18;');
        DB::statement('UPDATE `publisher` SET `site_name` = "fyiAuto", `site_code` = "fyiauto.com" WHERE publisher_id = 19;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Gotta Be Mobile", `site_code` = "gottabemobile.com" WHERE publisher_id = 20;');
        DB::statement('UPDATE `publisher` SET `site_name` = "TheUnlockr", `site_code` = "theunlockr.com" WHERE publisher_id = 21;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ForknPlate", `site_code` = "forknplate.com" WHERE publisher_id = 22;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Evleaks", `site_code` = "evleaks.at" WHERE publisher_id = 23;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Notebooks.com", `site_code` = "notebooks.com" WHERE publisher_id = 24;');
        DB::statement('UPDATE `publisher` SET `site_name` = "NextShark", `site_code` = "nextshark.com", `site_ga` = "UA-41929724-11" WHERE publisher_id = 25;');
        DB::statement('UPDATE `publisher` SET `site_name` = "FunChoke", `site_code` = "funchoke.com", `site_tb` = "bjpenn" WHERE publisher_id = 26;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BJPENN.com", `site_code` = "bjpenn.com", `site_ga` = "UA-41929724-13" WHERE publisher_id = 27;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Manhattan Foodie Reviews", `site_code` = "manhattanfoodiereviews.com", `site_tb` = "thepublisherdesk-tabtimes" WHERE publisher_id = 28;');
        DB::statement('UPDATE `publisher` SET `site_name` = "TabTimes", `site_code` = "tabtimes.com", `site_ga` = "UA-41929724-14" WHERE publisher_id = 29;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Sound Guys", `site_code` = "soundguys.com", `site_ga` = "UA-41929724-15" WHERE publisher_id = 30;');
        DB::statement('UPDATE `publisher` SET `site_name` = "COED", `site_code` = "coed.com" WHERE publisher_id = 31;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Busted Coverage", `site_code` = "bustedcoverage.com" WHERE publisher_id = 32;');
        DB::statement('UPDATE `publisher` SET `site_name` = "College Candy", `site_code` = "collegecandy.com" WHERE publisher_id = 33;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Patriot Newswire", `site_code` = "patriotnewswire.com", `site_ga` = "UA-41929724-21" WHERE publisher_id = 34;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Three Percenter Nation", `site_code` = "threepercenternation.com", `site_ga` = "UA-41929724-22", `site_tb` = "thepublisherdesk-joeforamerica" WHERE publisher_id = 35;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Joe For America", `site_code` = "joeforamerica.com", `site_ga` = "UA-41929724-23" WHERE publisher_id = 36;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Pamela Geller", `site_code` = "pamelageller.com", `site_ga` = "UA-41929724-24" WHERE publisher_id = 37;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Universal Free Press", `site_code` = "universalfreepress.com", `site_ga` = "UA-41929724-20" WHERE publisher_id = 38;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Clash Daily", `site_code` = "clashdaily.com", `site_ga` = "UA-41929724-25" WHERE publisher_id = 39;');
        DB::statement('UPDATE `publisher` SET `site_name` = "DC Gazette", `site_code` = "dcgazette.com", `site_ga` = "UA-41929724-26" WHERE publisher_id = 40;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Allen B West", `site_code` = "allenbwest.com", `site_ga` = "UA-41929724-27" WHERE publisher_id = 41;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Freedom Outpost", `site_code` = "freedomoutpost.com", `site_ga` = "UA-41929724-28" WHERE publisher_id = 42;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BrightScope", `site_code` = "brightscope.com", `site_ga` = "UA-41929724-19" WHERE publisher_id = 43;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Publisher Desk", `site_code` = "publisherdesk.com" WHERE publisher_id = 44;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Motor Review", `site_code` = "motorreview.com" WHERE publisher_id = 45;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Money Nation", `site_code` = "moneynation.com" WHERE publisher_id = 46;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Practically Viral", `site_code` = "practicallyviral.com", `site_ga` = "UA-41929724-29" WHERE publisher_id = 47;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Social Media Today", `site_code` = "socialmediatoday.com", `site_ga` = "UA-41929724-30" WHERE publisher_id = 48;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Wine On The Street", `site_code` = "wineonthestreet.com" WHERE publisher_id = 49;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Wounded American Warrior", `site_code` = "woundedamericanwarrior.com" WHERE publisher_id = 50;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Consciously Enlightened", `site_code` = "consciouslyenlightened.com" WHERE publisher_id = 51;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Allen West Republic", `site_code` = "allenwestrepublic.com" WHERE publisher_id = 52;');
        DB::statement('UPDATE `publisher` SET `site_name` = "100% Fed Up", `site_code` = "100percentfedup.com" WHERE publisher_id = 53;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Unique Homes", `site_code` = "uniquehomes.com" WHERE publisher_id = 54;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The PolitiStick", `site_code` = "politistick.com", `site_tb` = "thepublisherdesk-patriotupdate" WHERE publisher_id = 55;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Patriot Update", `site_code` = "patriotupdate.com", `site_tb` = "thepublisherdesk-conservativebyte" WHERE publisher_id = 56;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Conservative Byte", `site_code` = "conservativebyte.com" WHERE publisher_id = 57;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Convervative Videos", `site_code` = "conservativevideos.com" WHERE publisher_id = 58;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Active Times", `site_code` = "theactivetimes.com" WHERE publisher_id = 59;');
        DB::statement('UPDATE `publisher` SET `site_name` = "PCHgames", `site_code` = "games.pch.com" WHERE publisher_id = 60;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Daily Meal", `site_code` = "thedailymeal.com", `site_ga` = "UA-41929724-31" WHERE publisher_id = 61;');
        DB::statement('UPDATE `publisher` SET `site_name` = "MMA.tv", `site_code` = "mma.tv" WHERE publisher_id = 62;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ENT Imports", `site_code` = "entimports.com" WHERE publisher_id = 63;');
        DB::statement('UPDATE `publisher` SET `site_name` = "US Herald", `site_code` = "usherald.com" WHERE publisher_id = 64;');
        DB::statement('UPDATE `publisher` SET `site_name` = "RecipeGirl", `site_code` = "recipegirl.com" WHERE publisher_id = 65;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BJ Penn Hawaii News", `site_code` = "bjpennhawaiinews.com", `site_tb` = "thepublisherdesk-carthrottle" WHERE publisher_id = 66;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Car Throttle Limited", `site_code` = "carthrottle.com" WHERE publisher_id = 67;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ECT News Network", `site_code` = "ectnews.com" WHERE publisher_id = 68;');
        DB::statement('UPDATE `publisher` SET `site_name` = "E-Commerce Times", `site_code` = "ecommercetimes.com" WHERE publisher_id = 69;');
        DB::statement('UPDATE `publisher` SET `site_name` = "TechNewsWorld", `site_code` = "technewsworld.com" WHERE publisher_id = 70;');
        DB::statement('UPDATE `publisher` SET `site_name` = "CRM Buyer", `site_code` = "crmbuyer.com" WHERE publisher_id = 71;');
        DB::statement('UPDATE `publisher` SET `site_name` = "LinuxInsider", `site_code` = "linuxinsider.com", `site_tb` = "thepublisherdesk-dailydetroit" WHERE publisher_id = 72;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Daily Detroit", `site_code` = "dailydetroit.com" WHERE publisher_id = 73;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BJ Penn - Videos", `site_code` = "bjpenn.com.videos" WHERE publisher_id = 74;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Draft Site", `site_code` = "draftsite.com" WHERE publisher_id = 75;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ecoustics", `site_code` = "ecoustics.com" WHERE publisher_id = 76;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Eagle Rising", `site_code` = "eaglerising.com" WHERE publisher_id = 77;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Freedom Daily", `site_code` = "freedomdaily.com" WHERE publisher_id = 78;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Tasty Trix", `site_code` = "tastytrix.blogspot.com" WHERE publisher_id = 79;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Wild Greens and Sardines", `site_code` = "wildgreensandsardines.com" WHERE publisher_id = 80;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Proud Conservative", `site_code` = "proudcons.com" WHERE publisher_id = 81;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Patriot Chronicle", `site_code` = "patriotchronicle.com" WHERE publisher_id = 82;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Freedom Outpost", `site_code` = "freedomoutpost.com", `site_tb` = "thepublisherdesk-constitution" WHERE publisher_id = 83;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Constitution.com", `site_code` = "constitution.com" WHERE publisher_id = 84;');
        DB::statement('UPDATE `publisher` SET `site_name` = "DennisMichaelLynch.com", `site_code` = "dennismichaellynch.com" WHERE publisher_id = 85;');
        DB::statement('UPDATE `publisher` SET `site_name` = "My Suburban Kitchen", `site_code` = "mysuburbankitchen.com" WHERE publisher_id = 86;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Girls Just Wanna Have Guns", `site_code` = "girlsjustwannahaveguns.com" WHERE publisher_id = 87;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Real Side", `site_code` = "therealside.com" WHERE publisher_id = 88;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BarbWire.com with Matt Barber", `site_code` = "barbwire.com" WHERE publisher_id = 89;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Black Sphere", `site_code` = "theblacksphere.net" WHERE publisher_id = 90;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Liberty Alliance", `site_code` = "libertyalliance.com", `site_tb` = "thepublisherdesk-fightingfortrump" WHERE publisher_id = 91;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Fighting For Trump", `site_code` = "fightingfortrump.com" WHERE publisher_id = 92;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Matt Walsh Blog", `site_code` = "themattwalshblog.com" WHERE publisher_id = 93;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Minutemen News", `site_code` = "minutemennews.com" WHERE publisher_id = 94;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Shark Tank", `site_code` = "shark-tank.com" WHERE publisher_id = 95;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Conservative Intelligence Briefing", `site_code` = "conservativeintel.com" WHERE publisher_id = 96;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Sons of Liberty Media", `site_code` = "sonsoflibertymedia.com" WHERE publisher_id = 97;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Mental Recession", `site_code` = "menrec.com" WHERE publisher_id = 98;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Powdered Wig Society", `site_code` = "powderedwigsociety.com" WHERE publisher_id = 99;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Little Red Window", `site_code` = "littleredwindow.com", `site_tb` = "thepublisherdesk-mixedmartialarts" WHERE publisher_id = 100;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Mixed Martial Arts", `site_code` = "mixedmartialarts.com", `site_tb` = "thepublisherdesk-butterwithasideofbread" WHERE publisher_id = 101;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Butter with a Side of Bread", `site_code` = "butterwithasideofbread.com" WHERE publisher_id = 102;');
        DB::statement('UPDATE `publisher` SET `site_name` = "National Review", `site_code` = "nationalreview.com" WHERE publisher_id = 103;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Bill O\'Reilly", `site_code` = "billoreilly.com" WHERE publisher_id = 104;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Conservative Firing Line", `site_code` = "conservativefiringline.com" WHERE publisher_id = 105;');
        DB::statement('UPDATE `publisher` SET `site_name` = "NewsNinja Wayne Dupree", `site_code` = "newsninja2012.com" WHERE publisher_id = 106;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Outdoor Beasts", `site_code` = "outdoorbeasts.com" WHERE publisher_id = 107;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Daily Surge", `site_code` = "dailysurge.com" WHERE publisher_id = 108;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Red Hot Cha Cha", `site_code` = "redhotchacha.com" WHERE publisher_id = 109;');
        DB::statement('UPDATE `publisher` SET `site_name` = "PolitiChicks", `site_code` = "politichicks.com" WHERE publisher_id = 110;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Zionica", `site_code` = "zionica.com" WHERE publisher_id = 111;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Jan Morgan Media", `site_code` = "janmorganmedia.com" WHERE publisher_id = 112;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Instigator News", `site_code` = "instigatornews.com" WHERE publisher_id = 113;');
        DB::statement('UPDATE `publisher` SET `site_name` = "All Things Vegas", `site_code` = "allthingsvegas.com" WHERE publisher_id = 114;');
        DB::statement('UPDATE `publisher` SET `site_name` = "GenFringe.com", `site_code` = "genfringe.com" WHERE publisher_id = 115;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Red Maryland", `site_code` = "redmaryland.com" WHERE publisher_id = 116;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Evan Sayet", `site_code` = "evansayet.com" WHERE publisher_id = 117;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Tavern Keepers", `site_code` = "tavernkeepers.com" WHERE publisher_id = 118;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Lid", `site_code` = "lidblog.com" WHERE publisher_id = 119;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Rabble Writer", `site_code` = "rabblewriter.com" WHERE publisher_id = 120;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Deneen Borelli", `site_code` = "deneenborelli.com" WHERE publisher_id = 121;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Sticky Doorknobs", `site_code` = "stickydoorknobs.com" WHERE publisher_id = 122;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Victoria Jackson", `site_code` = "victoriajackson.com" WHERE publisher_id = 123;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Angry White Dude", `site_code` = "angrywhitedude.com" WHERE publisher_id = 124;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Iowa Statesman", `site_code` = "theiowastatesman.com" WHERE publisher_id = 125;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Pit Grit", `site_code` = "pitgrit.com" WHERE publisher_id = 126;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Daily LOL", `site_code` = "dailylol.com" WHERE publisher_id = 127;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Gun Debate", `site_code` = "gundebate.com", `site_tb` = "thepublisherdesk-bestfights" WHERE publisher_id = 128;');
        DB::statement('UPDATE `publisher` SET `site_name` = "BestFights.tv", `site_code` = "bestfights.tv", `site_tb` = "thepublisherdesk-wellsquad" WHERE publisher_id = 129;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Well Squad", `site_code` = "wellsquad.com" WHERE publisher_id = 130;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Lineup", `site_code` = "the-line-up.com" WHERE publisher_id = 131;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Fine Cooking", `site_code` = "finecooking.com" WHERE publisher_id = 132;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Tab", `site_code` = "thetab.com", `site_tb` = "thepublisherdesk-minds" WHERE publisher_id = 133;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Minds", `site_code` = "minds.com", `site_tb` = "thepublisherdesk-coolfords" WHERE publisher_id = 134;');
        DB::statement('UPDATE `publisher` SET `site_name` = "CoolFords.com", `site_code` = "coolfords.com", `site_tb` = "thepublisherdesk-chevytv" WHERE publisher_id = 135;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ChevyTV.com", `site_code` = "chevytv.com", `site_tb` = "thepublisherdesk-vettetv" WHERE publisher_id = 136;');
        DB::statement('UPDATE `publisher` SET `site_name` = "VetteTV.com", `site_code` = "vettetv.com" WHERE publisher_id = 137;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Tyre Reviews", `site_code` = "tyrereviews.co.uk", `site_tb` = "thepublisherdesk-justluxe" WHERE publisher_id = 138;');
        DB::statement('UPDATE `publisher` SET `site_name` = "JustLuxe", `site_code` = "justluxe.com", `site_tb` = "thepublisherdesk-socialmediaexplorer" WHERE publisher_id = 139;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Social Media Explorer", `site_code` = "socialmediaexplorer.com", `site_tb` = "thepublisherdesk-louderwithcrowder" WHERE publisher_id = 140;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Louder With Crowder", `site_code` = "louderwithcrowder.com" WHERE publisher_id = 141;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Portalist", `site_code` = "theportalist.com" WHERE publisher_id = 142;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Early Bird Books", `site_code` = "earlybirdbooks.com" WHERE publisher_id = 143;');
        DB::statement('UPDATE `publisher` SET `site_name` = "CNET", `site_code` = "cnet.com", `site_tb` = "thepublisherdesk-thterrortime" WHERE publisher_id = 144;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Tom Holland\'s Terror Time", `site_code` = "thterrortime.com" WHERE publisher_id = 145;');
        DB::statement('UPDATE `publisher` SET `site_name` = "iPatriot", `site_code` = "ipatriot.com" WHERE publisher_id = 146;');
        DB::statement('UPDATE `publisher` SET `site_name` = "LowKick MMA", `site_code` = "lowkickmma.com" WHERE publisher_id = 147;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Scrap Digest", `site_code` = "scrapdigest.com", `site_tb` = "thepublisherdesk-pasionfutbol" WHERE publisher_id = 148;');
        DB::statement('UPDATE `publisher` SET `site_name` = "PasionFutbol", `site_code` = "pasionfutbol.com", `site_tb` = "thepublisherdesk-studentrecipes" WHERE publisher_id = 149;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Student Recipes", `site_code` = "studentrecipes.com" WHERE publisher_id = 150;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Mixed Martial Arts - App", `site_code` = "mixedmartialarts.app" WHERE publisher_id = 151;');
        DB::statement('UPDATE `publisher` SET `site_name` = "WTF1", `site_code` = "wtf1.co.uk" WHERE publisher_id = 152;');
        DB::statement('UPDATE `publisher` SET `site_name` = "TheSun", `site_code` = "thesun.co.uk", `site_tb` = "thepublisherdesk-thegrio" WHERE publisher_id = 153;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Grio", `site_code` = "thegrio.com" WHERE publisher_id = 154;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Speedlist", `site_code` = "speedlist.com", `site_tb` = "thepublisherdesk-vrsource" WHERE publisher_id = 155;');
        DB::statement('UPDATE `publisher` SET `site_name` = "VRSource", `site_code` = "vrsource.com", `site_tb` = "thepublisherdesk-chargedio" WHERE publisher_id = 156;');
        DB::statement('UPDATE `publisher` SET `site_name` = "ChargedIO", `site_code` = "charged.io", `site_tb` = "thepublisherdesk-waynedupree" WHERE publisher_id = 157;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Wayne Dupree", `site_code` = "waynedupree.com" WHERE publisher_id = 158;');
        DB::statement('UPDATE `publisher` SET `site_name` = "SportsNaut", `site_code` = "sportsnaut.com" WHERE publisher_id = 159;');
        DB::statement('UPDATE `publisher` SET `site_name` = "wtf1.com", `site_code` = "wtf1.com" WHERE publisher_id = 160;');
        DB::statement('UPDATE `publisher` SET `site_name` = "The Times", `site_code` = "thetimes.co.uk" WHERE publisher_id = 161;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Against Dispensationalism", `site_code` = "againstdispensationalism.com" WHERE publisher_id = 162;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Silence is Consent", `site_code` = "silenceisconsent.net" WHERE publisher_id = 163;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Survival Nation", `site_code` = "survivalnation.com" WHERE publisher_id = 164;');
        DB::statement('UPDATE `publisher` SET `site_name` = "bb4sp", `site_code` = "bb4sp.com" WHERE publisher_id = 165;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Bullets First", `site_code` = "bulletsfirst.net" WHERE publisher_id = 166;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Cactus Politics", `site_code` = "cactuspolitics.com" WHERE publisher_id = 167;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Jason Mattera", `site_code` = "jasonmattera.com" WHERE publisher_id = 169;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Joseph C Phillips", `site_code` = "josephcphillips.com" WHERE publisher_id = 170;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Keep and Bear", `site_code` = "keepandbear.com" WHERE publisher_id = 171;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Liberty Unyielding", `site_code` = "libertyunyielding.com" WHERE publisher_id = 172;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Political Mayhem", `site_code` = "politicalmayhem.news" WHERE publisher_id = 174;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Quin Hillyer", `site_code` = "quinhillyer.com" WHERE publisher_id = 176;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Revive USA", `site_code` = "reviveusa.com" WHERE publisher_id = 177;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Red Right Republic", `site_code` = "redrightrepublic.com", `site_tb` = "thepublisherdesk-lynxmedia" WHERE publisher_id = 178;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Lynx Media", `site_code` = "lynx.media" WHERE publisher_id = 179;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Think Americana", `site_code` = "thinkamericana.com" WHERE publisher_id = 180;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Dev Environment", `site_code` = "knappyearseve2016.com" WHERE publisher_id = 181;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Conservative Review", `site_code` = "conservativereview.com" WHERE publisher_id = 182;');
        DB::statement('UPDATE `publisher` SET `site_name` = "Poker Central", `site_code` = "pokercentral.com" WHERE publisher_id = 183;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `publisher` DROP COLUMN `site_name`;");
        DB::statement("ALTER TABLE `publisher` DROP COLUMN `site_code`;");
        DB::statement("ALTER TABLE `publisher` DROP COLUMN `site_ga`;");
        DB::statement("ALTER TABLE `publisher` DROP COLUMN `site_tb`;");
    }
}
