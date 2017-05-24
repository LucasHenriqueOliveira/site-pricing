<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySourceStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('source_status', function (Blueprint $table) {
            $table->tinyInteger('error_code')->nullable()->after('status');
        });
        Schema::table('source_status', function (Blueprint $table) {
            $table->string('error_description')->nullable()->after('error_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('source_status', function (Blueprint $table) {
            $table->dropColumn('error_code');
            $table->dropColumn('error_description');
        });
    }
}
