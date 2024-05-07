<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSallaCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salla_cities', function (Blueprint $table) {
            $table->id();
            $table->id('salla_id')->nullabale();
            $table->string('salla_name')->nullabale();
            $table->string('salla_name_en')->nullabale();
            $table->string('country_id')->nullabale();
            $table->unsignedBigInteger('sub_area_id')->nullable();
            $table->foreign('sub_area_id')->references('id')->on('sub_areas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salla_cities');
    }
}
