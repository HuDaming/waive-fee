<?php

namespace App\Http\Controllers;

use Alipay;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Exceptions\InvalidRequestException;

class OrdersController extends Controller
{
    /**
     * 下单页面
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws InvalidRequestException
     */
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

    /**
     * 线上资金授权
     *
     * @param OrderRequest $request
     * @return Order|\Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(OrderRequest $request)
    {
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

        $result = Alipay::fundAuthOrderAppFreeze($query);
        if (empty($result->code) && $result->code == 10000) {
            // 成功
            return new Order($query + [
                    'contact_name' => $request->contact_name,
                    'contact_mobile' => $request->contact_mobile,
                    'address' => $request->province . $request->city . $request->district . $request->address,
                    'remark' => $request->remark,
                    'status' => Order::STATUS_FREEZE,
                    'user_id' => $user->id,
                    'seller_id' => $request->seller_id,
                    'product_id' => $product->id
                ]);
        } else {
            return response()->json(['code' => $result->code, 'msg' => $result->sub_msg]);
        }
    }

    public function tradePay(Request $request)
    {
        $user = $request->user();

        $query = [
            'out_order_no' => Order::findAvailableOrderNo(),
            'scene' => '',
            'auth_code' => '',
            'product_code' => '',
            'subject' => '',
            'buyer_id' => '',
            'seller_id' => config('services.alipay.merchant_id'),
            'total_amount' => '',
            'trans_currency' => 'CNY', // 默认人名币
            'settle_currency' => 'CNY', // 默认人名币
            'discountable_amount' => 8.88, // 参与优惠计算的金额
            'body' => '', // 订单描述
            'goods_detail' => json_encode([
                'goods_id' => '', // 商品编号
                'goods_name' => '', // 商品名称
                'quantity' => '', // 数量
                'price' => '', // 单价
                'goods_category' => '', // 商品类目
                'categories_tree' => '124868003|126232002|126252004', // 商品类目树
                'body' => '', // 商品描述信息
                'show_url' => '', // 商品展示地址
            ], true),
            'operator_id' => '', // 商户操作员编号
            'store_id' => '', // 商户门号编号
            'terminal_id' => '', // 商户机具终端编号
            'extend_params' => json_encode([
                'sys_service_provider_id' => '2088511833207846', // 西涌上编号
                // 行业数据回流信息
                'industry_reflux_info' => json_encode([
                    'scene_code' => 'metro_tradeorder',
                    'channel' => 'xxxx',
                    'scene_data' => json_encode([
                        'asset_name' => 'ALIPAY'
                    ], true),
                ], true),
                'card_type' => 'S0JP0000', // 卡类型
            ], true),
            'timeout_express' => '9m',
            'auth_confirm_mode' => 'COMPLETE',
            'terminal_params' => json_encode(['key' => 'value'], true),
            'promo_params' => json_encode([
                'actual_order_time' => '2018-09-25 22:47:33',
            ], true),
            'advance_payment_type' => 'ENJOY_PAY_V2',
            'query_options' => '["fund_bill_list","voucher_detail_list","discount_goods_detail"]',
            'request_org_pid' => '2088201916734621',
            'is_async_pay' => false,
        ];

        $result = Alipay::tradePay($query);
        if (empty($result->code) && $result->code == 10000) {
            // 成功
            return [];
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
