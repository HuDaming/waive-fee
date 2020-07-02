<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNickNameAvatarOpenIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('open_id')->nullable()->unique()->after('id');
            $table->string('avatar')->nullable()->after('name');
            $table->string('nickname')->nullable()->after('avatar');
            $table->unsignedTinyInteger('gender', false)->default(0)->after('nickname');
            $table->string('city', 32)->nullable()->after('nickname');
            $table->string('province', 32)->nullable()->after('nickname');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['open_id', 'avatar', 'nickname', 'gender', 'city', 'province']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
        });
    }
}
