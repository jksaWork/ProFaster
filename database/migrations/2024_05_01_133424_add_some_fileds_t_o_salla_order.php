<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFiledsTOSallaOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('salla_orders', function (Blueprint $table) {
            $table->string('policy_file')->nullable();
            $table->string('receiver_country')->nullable();
            $table->string('receiver_country_code')->nullable();
            $table->string('receiver_address_line')->nullable();
            $table->string('receiver_street_number')->nullable();
            $table->string('receiver_block')->nullable();
            $table->string('receiver_city')->nullable();
            $table->string('receiver_postal_code')->nullable();
            
        });
    
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
