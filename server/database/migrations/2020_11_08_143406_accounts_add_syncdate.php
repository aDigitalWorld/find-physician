<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AccountsAddSyncdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->date('zoho_sync_date')->nullable()->after('delivery_date');
            $table->date('zoho_created_at')->nullable()->after('zoho_sync_date');
            $table->date('zoho_modified_at')->nullable()->after('zoho_created_at');
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
            $table->dropColumn('zoho_sync_date');
            $table->dropColumn('zoho_modified_at');
            $table->dropColumn('zoho_created_at');
        });
    }
}
