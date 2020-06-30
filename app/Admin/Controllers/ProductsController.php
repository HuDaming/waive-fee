<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Products;
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
        return Grid::make(new Products(), function (Grid $grid) {
            $grid->id->sortable();
            $grid->name;
            $grid->intro;
            $grid->banner_img->image();
            $grid->background_img->image();
            $grid->price;
            $grid->on_sale->switch('green');
            $grid->created_at;
            $grid->updated_at->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

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

            $form->text('name')
                ->creationRules('required|unique:products,name')
                ->updateRules('required|unique:products,name,{{id}}');

            $form->textarea('intro');

            $form->image('banner_img')->uniqueName()->rules('mimes:jpeg,bmp,png|max:2024');
            $form->image('background_img')->uniqueName()->rules('mimes:jpeg,bmp,png|max:2024');
            $form->currency('price')->symbol('¥')->rules('required|numeric|min:0');
            $form->switch('on_sale')->options([0, 1])->default(1)->green();

            $form->display('created_at');
            $form->display('updated_at');

            $form->disableResetButton();
            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();
        });
    }
}
