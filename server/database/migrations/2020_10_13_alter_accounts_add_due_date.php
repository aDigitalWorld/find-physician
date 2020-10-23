<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AccountsAddDueDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->date('training_date_new')->nullable()->after('training_date');
            $table->date('delivery_date')->nullable()->after('training_date_new');
            $table->date('install_date')->nullable()->after('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('delivery_date');
            $table->dropColumn('install_date');
            $table->dropColumn('training_date_new');
        });
    }
}
