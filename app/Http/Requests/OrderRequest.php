<?php

namespace App\Http\Requests;

use App\Models\Product;
use Dcat\Admin\Models\Administrator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class OrderRequest
 *
 * @property string $contact_name
 * @property string $contact_mobile
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $address
 * @property string $remark
 * @property Product $product
 * @package App\Http\Requests
 */
class OrderRequest extends FormRequest
{
    public $product;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->product = Product::find($value)) {
                        return $fail('商品不存在');
                    }
                    if (!$this->product->on_sale) {
                        return $fail('商品未上架');
                    }

                    return true;
                }
            ],
            'seller_id' => ['required', function ($attribute, $value, $fail) {
                if ($seller = Administrator::find($value)) {
                    $fail('业务员不存在');
                }
            }],
            'contact_name' => 'required|max:32',
            'contact_mobile' => 'required|max:32',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'address' => 'required|string|max:32',
            'remark' => 'string|max:128'
        ];
    }

    public function attributes()
    {
        return [
            'product_id' => '商品编号',
            'seller_id' => '业务员编号',
            'contact_name' => '收货人姓名',
            'contact_mobile' => '联系手机',
            'province' => '省',
            'city' => '市',
            'district' => '地区',
            'address' => '详细地址',
            'remark' => '备注'
        ];
    }
}
