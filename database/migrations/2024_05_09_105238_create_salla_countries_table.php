<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSallaCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salla_countries', function (Blueprint $table) {
            $table->id();
            $table->string("salla_id")->nullable();
            $table->string("name")->nullable();
            $table->string("name_en")->nullable();
            $table->string("code")->nullable();
            $table->string("mobile_code")->nullable();
            $table->string("capital")->nullable();
            $table->unsignedBigInteger('"area_id"')->nullable();
            $table->foreign('"area_id"')->references('id')->on('areas');
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
        Schema::dropIfExists('salla_countries');
    }
}
