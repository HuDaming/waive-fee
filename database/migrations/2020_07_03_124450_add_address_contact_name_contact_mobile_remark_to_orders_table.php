<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressContactNameContactMobileRemarkToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('remark')->after('identity_params');
            $table->string('address')->after('identity_params');
            $table->string('contact_mobile', 20)->after('identity_params');
            $table->string('contact_name', 32)->after('identity_params');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['remark', 'address', 'contact_mobile', 'contact_name']);
        });
    }
}
