<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowNulls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('street')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state', 30)->nullable()->change();
            $table->string('zipcode', 15)->nullable()->change();
            $table->string('country', 30)->nullable()->change();
            $table->string('formatted_address')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('lat')->nullable()->change();
            $table->string('lng')->nullable()->change();
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
