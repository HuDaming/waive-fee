<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 32)->unique()->comment('订单流水号');
            $table->string('request_no', 32)->unique()->comment('请求流水号');
            $table->string('order_title')->comment('业务订单的简单描述');
            $table->float('amount', 11, 2)->default(0)->comment('金额');
            $table->string('product_code')->default(\App\Models\Order::CODE_PRE_AUTH)->comment('销售产品码');
            $table->string('payee_logon_id', 100)->nullable()->comment('收款方支付宝账号');
            $table->string('payee_user_id', 32)->nullable()->comment('收款方支付宝用户ID');
            $table->string('pay_timeout', 5)->default('5m')->comment('最晚付款时间');
            $table->string('extra_param', 300)->nullable()->comment('业务扩展参数');
            $table->string('scene_code', 128)->nullable()->comment('场景码');
            $table->string('trans_currency', 8)->nullable()->comment('标价币种');
            $table->string('settle_currency', 8)->nullable()->comment('结算币种');
            $table->string('enable_pay_channels', 128)->nullable()->comment('可使用的支付渠道');
            $table->string('identity_params', 300)->nullable()->comment('用户实名信息参数');
            $table->string('status', 16)->comment('订单状态');
            $table->unsignedBigInteger('user_id')->comment('下单用户ID');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('seller_id')->comment('销售员ID');
            $table->foreign('seller_id')->references('id')->on('admin_users')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->comment('产品ID');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->softDeletes();
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
        Schema::dropIfExists('orders');
    }
}
