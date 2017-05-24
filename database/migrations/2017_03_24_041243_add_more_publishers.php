<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMorePublishers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("INSERT INTO `publisher` (`publisher_id`, `name`)
        VALUES
            (1,'technobuffalo.com'),
            (4,'biossleepsystem.com'),
            (6,'caintv.com'),
            (7,'confitdent.com'),
            (8,'dailynewspo.com'),
            (9,'dlife.com'),
            (11,'dupontregistry.com.boats'),
            (13,'healthspo.com'),
            (14,'maxim.com'),
            (15,'sodahead.com'),
            (16,'thriftycity.com'),
            (17,'veria.com'),
            (18,'youngmarriedchic.com'),
            (19,'fyiauto.com'),
            (20,'gottabemobile.com'),
            (21,'theunlockr.com'),
            (22,'forknplate.com'),
            (23,'evleaks.at'),
            (24,'notebooks.com'),
            (26,'funchoke.com'),
            (27,'bjpenn.com'),
            (28,'manhattanfoodiereviews.com'),
            (31,'coed.com'),
            (32,'bustedcoverage.com'),
            (33,'collegecandy.com'),
            (34,'patriotnewswire.com'),
            (35,'threepercenternation.com'),
            (36,'joeforamerica.com'),
            (37,'pamelageller.com'),
            (38,'universalfreepress.com'),
            (39,'clashdaily.com'),
            (40,'dcgazette.com'),
            (41,'allenbwest.com'),
            (42,'freedomoutpost.com'),
            (44,'publisherdesk.com'),
            (45,'motorreview.com'),
            (46,'moneynation.com'),
            (47,'practicallyviral.com'),
            (49,'wineonthestreet.com'),
            (50,'woundedamericanwarrior.com'),
            (51,'consciouslyenlightened.com'),
            (52,'allenwestrepublic.com'),
            (54,'uniquehomes.com'),
            (55,'politistick.com'),
            (56,'patriotupdate.com'),
            (57,'conservativebyte.com'),
            (58,'conservativevideos.com'),
            (60,'games.pch.com'),
            (64,'usherald.com'),
            (65,'recipegirl.com'),
            (66,'bjpennhawaiinews.com'),
            (68,'ectnews.com'),
            (69,'ecommercetimes.com'),
            (70,'technewsworld.com'),
            (71,'crmbuyer.com'),
            (72,'linuxinsider.com'),
            (74,'bjpenn.com.videos'),
            (75,'draftsite.com'),
            (77,'eaglerising.com'),
            (78,'freedomdaily.com'),
            (79,'tastytrix.blogspot.com'),
            (80,'wildgreensandsardines.com'),
            (81,'proudcons.com'),
            (82,'patriotchronicle.com'),
            (83,'freedomoutpost.com'),
            (84,'constitution.com'),
            (85,'dennismichaellynch.com'),
            (86,'mysuburbankitchen.com'),
            (87,'girlsjustwannahaveguns.com'),
            (88,'therealside.com'),
            (89,'barbwire.com'),
            (90,'theblacksphere.net'),
            (91,'libertyalliance.com'),
            (92,'fightingfortrump.com'),
            (93,'themattwalshblog.com'),
            (94,'minutemennews.com'),
            (95,'shark-tank.com'),
            (96,'conservativeintel.com'),
            (97,'sonsoflibertymedia.com'),
            (98,'menrec.com'),
            (99,'powderedwigsociety.com'),
            (100,'littleredwindow.com'),
            (103,'nationalreview.com'),
            (105,'conservativefiringline.com'),
            (106,'newsninja2012.com'),
            (107,'outdoorbeasts.com'),
            (108,'dailysurge.com'),
            (109,'redhotchacha.com'),
            (110,'politichicks.com'),
            (111,'zionica.com'),
            (112,'janmorganmedia.com'),
            (113,'instigatornews.com'),
            (114,'allthingsvegas.com'),
            (115,'genfringe.com'),
            (116,'redmaryland.com'),
            (117,'evansayet.com'),
            (118,'tavernkeepers.com'),
            (119,'lidblog.com'),
            (120,'rabblewriter.com'),
            (121,'deneenborelli.com'),
            (122,'stickydoorknobs.com'),
            (123,'victoriajackson.com'),
            (124,'angrywhitedude.com'),
            (125,'theiowastatesman.com'),
            (126,'pitgrit.com'),
            (127,'dailylol.com'),
            (128,'gundebate.com'),
            (129,'bestfights.tv'),
            (130,'wellsquad.com'),
            (132,'finecooking.com'),
            (134,'minds.com'),
            (140,'socialmediaexplorer.com'),
            (142,'theportalist.com'),
            (143,'earlybirdbooks.com'),
            (144,'cnet.com'),
            (146,'ipatriot.com'),
            (147,'lowkickmma.com'),
            (153,'thesun.co.uk'),
            (154,'thegrio.com'),
            (155,'speedlist.com'),
            (159,'sportsnaut.com'),
            (160,'wtf1.com'),
            (161,'thetimes.co.uk'),
            (162,'againstdispensationalism.com'),
            (163,'silenceisconsent.net'),
            (164,'survivalnation.com'),
            (165,'bb4sp.com'),
            (166,'bulletsfirst.net'),
            (167,'cactuspolitics.com'),
            (169,'jasonmattera.com'),
            (170,'josephcphillips.com'),
            (171,'keepandbear.com'),
            (172,'libertyunyielding.com'),
            (174,'politicalmayhem.news'),
            (176,'quinhillyer.com'),
            (177,'reviveusa.com'),
            (178,'redrightrepublic.com'),
            (179,'lynx.media');");

    DB::statement("INSERT INTO `revenue_share` (`revenue_share_id`, `publisher_id`, `client_fraction`, `date_added`, `date_modified`)
    VALUES
        (39,1,0.8,'2017-03-24 04:29:17',NULL),
        (40,4,0.8,'2017-03-24 04:29:17',NULL),
        (41,6,0.8,'2017-03-24 04:29:17',NULL),
        (42,7,0.8,'2017-03-24 04:29:17',NULL),
        (43,8,0.8,'2017-03-24 04:29:17',NULL),
        (44,9,0.8,'2017-03-24 04:29:17',NULL),
        (45,11,0.8,'2017-03-24 04:29:17',NULL),
        (46,13,0.8,'2017-03-24 04:29:17',NULL),
        (47,14,0.8,'2017-03-24 04:29:17',NULL),
        (48,15,0.8,'2017-03-24 04:29:17',NULL),
        (49,16,0.8,'2017-03-24 04:29:17',NULL),
        (50,17,0.8,'2017-03-24 04:29:17',NULL),
        (51,18,0.8,'2017-03-24 04:29:17',NULL),
        (52,19,0.8,'2017-03-24 04:29:17',NULL),
        (53,20,0.8,'2017-03-24 04:29:17',NULL),
        (54,21,0.8,'2017-03-24 04:29:17',NULL),
        (55,22,0.8,'2017-03-24 04:29:17',NULL),
        (56,23,0.8,'2017-03-24 04:29:17',NULL),
        (57,24,0.8,'2017-03-24 04:29:17',NULL),
        (58,26,0.8,'2017-03-24 04:29:17',NULL),
        (59,27,0.8,'2017-03-24 04:29:17',NULL),
        (60,28,0.8,'2017-03-24 04:29:17',NULL),
        (61,31,0.8,'2017-03-24 04:29:17',NULL),
        (62,32,0.8,'2017-03-24 04:29:17',NULL),
        (63,33,0.8,'2017-03-24 04:29:17',NULL),
        (64,34,0.8,'2017-03-24 04:29:17',NULL),
        (65,35,0.8,'2017-03-24 04:29:17',NULL),
        (66,36,0.8,'2017-03-24 04:29:17',NULL),
        (67,37,0.8,'2017-03-24 04:29:17',NULL),
        (68,38,0.8,'2017-03-24 04:29:17',NULL),
        (69,39,0.8,'2017-03-24 04:29:17',NULL),
        (70,40,0.8,'2017-03-24 04:29:17',NULL),
        (71,41,0.8,'2017-03-24 04:29:17',NULL),
        (72,42,0.8,'2017-03-24 04:29:17',NULL),
        (73,44,0.8,'2017-03-24 04:29:17',NULL),
        (74,45,0.8,'2017-03-24 04:29:17',NULL),
        (75,46,0.8,'2017-03-24 04:29:17',NULL),
        (76,47,0.8,'2017-03-24 04:29:17',NULL),
        (77,49,0.8,'2017-03-24 04:29:17',NULL),
        (78,50,0.8,'2017-03-24 04:29:17',NULL),
        (79,51,0.8,'2017-03-24 04:29:17',NULL),
        (80,52,0.8,'2017-03-24 04:29:17',NULL),
        (81,54,0.8,'2017-03-24 04:29:17',NULL),
        (82,55,0.8,'2017-03-24 04:29:17',NULL),
        (83,56,0.8,'2017-03-24 04:29:17',NULL),
        (84,57,0.8,'2017-03-24 04:29:17',NULL),
        (85,58,0.8,'2017-03-24 04:29:17',NULL),
        (86,60,0.8,'2017-03-24 04:29:17',NULL),
        (87,64,0.8,'2017-03-24 04:29:17',NULL),
        (88,65,0.8,'2017-03-24 04:29:17',NULL),
        (89,66,0.8,'2017-03-24 04:29:17',NULL),
        (90,68,0.8,'2017-03-24 04:29:17',NULL),
        (91,69,0.8,'2017-03-24 04:29:17',NULL),
        (92,70,0.8,'2017-03-24 04:29:17',NULL),
        (93,71,0.8,'2017-03-24 04:29:17',NULL),
        (94,72,0.8,'2017-03-24 04:29:17',NULL),
        (95,74,0.8,'2017-03-24 04:29:17',NULL),
        (96,75,0.8,'2017-03-24 04:29:17',NULL),
        (97,77,0.8,'2017-03-24 04:29:17',NULL),
        (98,78,0.8,'2017-03-24 04:29:17',NULL),
        (99,79,0.8,'2017-03-24 04:29:17',NULL),
        (100,80,0.8,'2017-03-24 04:29:17',NULL),
        (101,81,0.8,'2017-03-24 04:29:17',NULL),
        (102,82,0.8,'2017-03-24 04:29:17',NULL),
        (103,83,0.8,'2017-03-24 04:29:17',NULL),
        (104,84,0.8,'2017-03-24 04:29:17',NULL),
        (105,85,0.8,'2017-03-24 04:29:17',NULL),
        (106,86,0.8,'2017-03-24 04:29:17',NULL),
        (107,87,0.8,'2017-03-24 04:29:17',NULL),
        (108,88,0.8,'2017-03-24 04:29:17',NULL),
        (109,89,0.8,'2017-03-24 04:29:17',NULL),
        (110,90,0.8,'2017-03-24 04:29:17',NULL),
        (111,91,0.8,'2017-03-24 04:29:17',NULL),
        (112,92,0.8,'2017-03-24 04:29:17',NULL),
        (113,93,0.8,'2017-03-24 04:29:17',NULL),
        (114,94,0.8,'2017-03-24 04:29:17',NULL),
        (115,95,0.8,'2017-03-24 04:29:17',NULL),
        (116,96,0.8,'2017-03-24 04:29:17',NULL),
        (117,97,0.8,'2017-03-24 04:29:17',NULL),
        (118,98,0.8,'2017-03-24 04:29:17',NULL),
        (119,99,0.8,'2017-03-24 04:29:17',NULL),
        (120,100,0.8,'2017-03-24 04:29:17',NULL),
        (121,103,0.8,'2017-03-24 04:29:17',NULL),
        (122,105,0.8,'2017-03-24 04:29:17',NULL),
        (123,106,0.8,'2017-03-24 04:29:17',NULL),
        (124,107,0.8,'2017-03-24 04:29:17',NULL),
        (125,108,0.8,'2017-03-24 04:29:17',NULL),
        (126,109,0.8,'2017-03-24 04:29:17',NULL),
        (127,110,0.8,'2017-03-24 04:29:17',NULL),
        (128,111,0.8,'2017-03-24 04:29:17',NULL),
        (129,112,0.8,'2017-03-24 04:29:17',NULL),
        (130,113,0.8,'2017-03-24 04:29:17',NULL),
        (131,114,0.8,'2017-03-24 04:29:17',NULL),
        (132,115,0.8,'2017-03-24 04:29:17',NULL),
        (133,116,0.8,'2017-03-24 04:29:17',NULL),
        (134,117,0.8,'2017-03-24 04:29:17',NULL),
        (135,118,0.8,'2017-03-24 04:29:17',NULL),
        (136,119,0.8,'2017-03-24 04:29:17',NULL),
        (137,120,0.8,'2017-03-24 04:29:17',NULL),
        (138,121,0.8,'2017-03-24 04:29:17',NULL),
        (139,122,0.8,'2017-03-24 04:29:17',NULL),
        (140,123,0.8,'2017-03-24 04:29:17',NULL),
        (141,124,0.8,'2017-03-24 04:29:17',NULL),
        (142,125,0.8,'2017-03-24 04:29:17',NULL),
        (143,126,0.8,'2017-03-24 04:29:17',NULL),
        (144,127,0.8,'2017-03-24 04:29:17',NULL),
        (145,128,0.8,'2017-03-24 04:29:17',NULL),
        (146,129,0.8,'2017-03-24 04:29:17',NULL),
        (147,130,0.8,'2017-03-24 04:29:17',NULL),
        (148,132,0.8,'2017-03-24 04:29:17',NULL),
        (149,134,0.8,'2017-03-24 04:29:17',NULL),
        (150,140,0.8,'2017-03-24 04:29:17',NULL),
        (151,142,0.8,'2017-03-24 04:29:17',NULL),
        (152,143,0.8,'2017-03-24 04:29:17',NULL),
        (153,144,0.8,'2017-03-24 04:29:17',NULL),
        (154,146,0.8,'2017-03-24 04:29:17',NULL),
        (155,147,0.8,'2017-03-24 04:29:17',NULL),
        (156,153,0.8,'2017-03-24 04:29:17',NULL),
        (157,154,0.8,'2017-03-24 04:29:17',NULL),
        (158,155,0.8,'2017-03-24 04:29:17',NULL),
        (159,159,0.8,'2017-03-24 04:29:17',NULL),
        (160,160,0.8,'2017-03-24 04:29:17',NULL),
        (161,161,0.8,'2017-03-24 04:29:17',NULL),
        (162,162,0.8,'2017-03-24 04:29:17',NULL),
        (163,163,0.8,'2017-03-24 04:29:17',NULL),
        (164,164,0.8,'2017-03-24 04:29:17',NULL),
        (165,165,0.8,'2017-03-24 04:29:17',NULL),
        (166,166,0.8,'2017-03-24 04:29:17',NULL),
        (167,167,0.8,'2017-03-24 04:29:17',NULL),
        (168,169,0.8,'2017-03-24 04:29:17',NULL),
        (169,170,0.8,'2017-03-24 04:29:17',NULL),
        (170,171,0.8,'2017-03-24 04:29:17',NULL),
        (171,172,0.8,'2017-03-24 04:29:17',NULL),
        (172,174,0.8,'2017-03-24 04:29:17',NULL),
        (173,176,0.8,'2017-03-24 04:29:17',NULL),
        (174,177,0.8,'2017-03-24 04:29:17',NULL),
        (175,178,0.8,'2017-03-24 04:29:17',NULL),
        (176,179,0.8,'2017-03-24 04:29:17',NULL);");

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