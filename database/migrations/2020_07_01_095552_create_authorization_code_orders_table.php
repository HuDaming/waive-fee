<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorizationCodeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorization_code_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 32)->unique()->comment('订单流水号');
            $table->string('request_no', 32)->unique()->comment('请求流水号');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->text('extra')->nullable()->comment('订单详情');
            $table->float('price', 10, 2)->default(0)->comment('价格');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('admin_users')->onDelete('cascade');
            $table->boolean('status')->default(false)->comment('获取授权码状态');
            $table->string('message')->nullable()->comment('授权失败原因');
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
        Schema::dropIfExists('authorization_code_orders');
    }
}
