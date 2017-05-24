<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySourceStatusErrorCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `source_status`
            CHANGE COLUMN `error_code` `error_code_id` INT NULL DEFAULT NULL ,
            ADD INDEX `fk_error_code_idx` (`error_code_id` ASC);
        ");

        DB::statement("
            ALTER TABLE `source_status`
            ADD CONSTRAINT `fk_error_code_id`
              FOREIGN KEY (`error_code_id`)
              REFERENCES `error_code` (`error_code_id`)
              ON DELETE NO ACTION
              ON UPDATE NO ACTION;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            ALTER TABLE `source_status`
            DROP FOREIGN KEY `fk_error_code_id`;
        ");
        DB::statement("
            ALTER TABLE `source_status`
            DROP INDEX `fk_error_code_idx`;
        ");
        DB::statement("
            ALTER TABLE `source_status`
            CHANGE COLUMN `error_code_id` `error_code` INT NULL DEFAULT NULL;
        ");
    }
}
