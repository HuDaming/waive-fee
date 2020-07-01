<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeTransCurrencySettleCurrencyEnableChannelToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('code')->default(\App\Models\Product::CODE_PRE)->after('id')->comment('预售产品码');
            $table->string('trans_currency', 32)->nullable()->after('price')->comment('标价币种');
            $table->string('settle_currency', 32)->nullable()->after('trans_currency')->comment('结算币种');
            $table->string('enable_pay_channels')->after('settle_currency')->comment('可用支付渠道');
            $table->tinyInteger('pay_timeout')->default(0)->after('settle_currency')->comment('最晚履约时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['code', 'trans_currency', 'settle_currency', 'enable_pay_channels', 'pay_timeout']);
        });
    }
}
