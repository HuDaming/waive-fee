<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\CreateVoucherQrCode;
use App\Admin\Repositories\Products;
use App\Models\Product;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Controllers\AdminController;

class ProductsController extends AdminController
{
    protected $title = '产品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Products('users'), function (Grid $grid) {
            $grid->id->sortable();
            $grid->name;
            $grid->intro;
            $grid->banner_img->image();
            $grid->background_img->image();
            $grid->users('授权二维码')->where('id', \Admin::user()->id)->pluck('pivot.qr_code')->image();
            $grid->price;
            $grid->on_sale->switch('green');
            $grid->created_at;
            // $grid->updated_at->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new CreateVoucherQrCode());
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Products(), function (Show $show) {
            $show->id;
            $show->name;
            $show->intro;
            $show->banner_img;
            $show->background_img;
            $show->price;
            $show->on_sale;
            $show->created_at;
            $show->updated_at;
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Products(), function (Form $form) {
            $form->display('id');

            $form->row(function (Form\Row $form) {
                $form->radio('code')->options(Product::$codeMap);
            });

            $form->row(function (Form\Row $form) {
                $form->width(3)->text('name')
                    ->creationRules('required|unique:products,name')
                    ->updateRules('required|unique:products,name,{{id}}');

                $form->width(7)->textarea('intro');
            });

            $form->row(function (Form\Row $form) {
                $form->width(5)
                    ->image('banner_img')
                    ->uniqueName()
                    ->help('')
                    ->rules('mimes:jpeg,bmp,png|max:2024');
                $form->width(5)
                    ->image('background_img')
                    ->uniqueName()
                    ->help('防止变形请使用尺寸为 540*1082，2M以内的图片作为二维码背景图')
                    ->rules('mimes:jpeg,bmp,png|max:2024');
            });

            $form->row(function (Form\Row $form) {
                $form->width(3)
                    ->currency('price')
                    ->symbol('¥')
                    ->rules('required|numeric|min:0');
                $form->width(3)
                    ->select('trans_currency')
                    ->options(Product::$transCurrencyMap)
                    ->help('默认人民币');
                $form->width(3)
                    ->select('settle_currency')
                    ->options(Product::$transCurrencyMap)
                    ->help('默认人民币');
                $form->width(10)->multipleSelect('enable_pay_channels')->options(Product::$channelMap);
            });

            $form->row(function (Form\Row $form) {
                $form->width(2)
                    ->select('pay_timeout')
                    ->options(Product::getPayTimeoutDays())
                    ->help('支付宝限制 15 天')
                    ->rules('integer|min:1|max:15');
            });

            $form->row(function (Form\Row $form) {
                $form->width(2)->switch('on_sale')->options([0, 1])->default(1)->green();
            });

            $form->display('created_at');
            $form->display('updated_at');

            $form->disableResetButton();
            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();
        });
    }
}
