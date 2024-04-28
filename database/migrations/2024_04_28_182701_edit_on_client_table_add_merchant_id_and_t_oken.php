<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditOnClientTableAddMerchantIdAndTOken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'merchant_id')) {
                $table->string('access_token')->nullable(); 
                $table->string('merchant_id')->nullable(); 
                $table->string('expired_date')->nullable(); 
                $table->string('refresh_token')->nullable(); 
            }
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
