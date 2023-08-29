<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFormDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->renameColumn('landing_page_id', 'source_id');
            $table->string('type', 191)->default('landingpage');
            $table->string('url', 2048)->nullable();
            $table->string('location', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->renameColumn('source_id', 'landing_page_id');
            $table->dropColumn('type');
            $table->dropColumn('url');
            $table->dropColumn('location');
        });
        
    }
}
