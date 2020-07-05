<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Exceptions\InvalidRequestException;

class OrdersController extends Controller
{
    public function create(Request $request)
    {
        $product = Product::find($request->product_id);
        if (!$product) {
            throw new InvalidRequestException('商品不存在或者已下架');
        }

        return view('orders.create', [
            'user_id' => $request->input('user_id'),
            'seller_id' => $request->input('seller_id'),
            'product' => $product
        ]);
    }

    public function store(OrderRequest $request)
    {
        // 查询芝麻信用授权
        app('alipay')->zhimaAuthInfoQuery();

        $user = $request->user();
        $product = $request->product;
        $query = [
            'out_order_no' => Order::findAvailableOrderNo(),
            'out_request_no' => Order::findAvailableRequestNo(),
            'order_title' => '预授权冻结',
            'amount' => $product->price,
            'product_code' => $product->code,
            'payee_user_id' => config('services.alipay.merchant_id'),
            'pay_timeout' => $product->pay_timeout . 'd',
            'extra_param' => json_encode(['category' => 'CHARGE_PILE_CAR'], true),
            'scene_code' => 'ONLINE_AUTH_COMMON_SCENE',
        ];

        if (config('services.alipay.account'))
            $query['payee_logon_id'] = config('services.alipay.account'); // 支付宝登录账号

        if ($product->trans_currency) $query['trans_currency'] = $product->trans_currency;
        if ($product->settle_currency) $query['settle_currency'] = $product->settle_currency;
        // 支付渠道
        $query['enable_pay_channels'] = $this->getChannelsJson($product->enable_pay_channels);

        $result = app('alipay')->fundAuthOrderAppFreeze($query);
        if (empty($result->code) && $result->code == 10000) {
            // 成功
            $order = new Order($query + [
                    'contact_name' => $request->contact_name,
                    'contact_mobile' => $request->contact_mobile,
                    'address' => $request->province . $request->city . $request->district . $request->address,
                    'remark' => $request->remark,
                    'status' => Order::STATUS_FREEZE,
                    'user_id' => $user->id,
                    'seller_id' => $request->seller_id,
                    'product_id' => $product->id
                ]);

            return $order;
        } else {
            return response()->json(['code' => $result->code, 'msg' => $result->sub_msg]);
        }
    }

    /**
     * 拼接支付渠道
     *
     * @param array $channels
     * @return false|string
     */
    protected function getChannelsJson(array $channels = [])
    {
        $arr = [];
        foreach ($channels as $channel) {
            $arr[] = ['payChannelType' => $channel];
        }

        return json_encode($arr, true);
    }
}
