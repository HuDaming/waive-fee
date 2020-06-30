<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->unique()->comment('产品名称');
            $table->string('intro')->nullable()->comment('产品简介');
            $table->string('banner_img')->nullable()->comment('头图');
            $table->string('background_img')->nullable()->comment('二维码背景图');
            $table->float('price', 10, 2)->default(0)->comment('价格');
            $table->boolean('on_sale')->default(false)->comment('是否上架');
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
        Schema::dropIfExists('products');
    }
}
