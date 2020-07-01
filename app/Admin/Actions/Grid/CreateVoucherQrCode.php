<?php

namespace App\Admin\Actions\Grid;

use App\Models\AuthorizationCodeOrder;
use Dcat\Admin\Admin;
use Image;
use App\Models\Product;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CreateVoucherQrCode extends RowAction
{
    /**
     * @return string
     */
    protected $title = '生成二维码';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        /** @var Product $product */
        $product = Product::query()->find($this->getKey());

        // 获取二维码
        $codeUrl = $this->getAlipayQrCode($product);
        if (!$codeUrl) return $this->response()->error('授权二维码生成失败')->redirect('/products');

        // 合成二维码和背景图
        $file = $this->mergeImage($codeUrl, $product->full_background_img_url);

        try {
            $userId = Admin::user()->id;
            // 上传到七牛空间
            $disk = \Storage::disk('qiniu');
            $path = "qr_code/{$product->id}_{$userId}/" . md5($codeUrl) . '.jpg';
            $result = $disk->put($path, $file);
            if ($result) {
                // 保存用户这个商品二维码
                $product->users()->sync([$userId => ['qr_code' => $path]], false);
            } else {
                return $this->response()->error('二维码上传失败')->redirect('/products');
            }

        } catch (\Exception $e) {
            return $this->response()->error('二维码保存失败' . $e->getMessage())->redirect('/products');
        }

        return $this->response()->success('授权二维码生成成功')->redirect('/products');
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        // return ['Confirm?', 'contents'];
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }

    protected function html()
    {
        return <<<HTML
<a {$this->formatHtmlAttributes()}><i class="fa fa-qrcode"></i> {$this->title()}</a>
HTML;
    }

    protected function getAlipayQrCode(Product $product)
    {
        $orderNo = AuthorizationCodeOrder::findAvailableOrderNo();
        $requestNo = AuthorizationCodeOrder::findAvailableRequestNo();

        $query = [
            'out_order_no' => $orderNo,
            'out_request_no' => $requestNo,
            'order_title' => $product->name . '预授权发码',
            'amount' => $product->price,
            'payee_user_id' => config('services.alipay.merchant_id'),
            'product_code' => $product->code,
            'enable_pay_channels' => $this->getChannelsJson($product->enable_pay_channels),
            'pay_timeout' => $product->pay_timeout . 'd',
            'extra_param' => json_encode(['category' => 'HOME', 'requestOrgId' => Admin::user()->id], true),
            //'identity_params' => json_encode(['identity_hash' => 'ABCDEFDxxxxxx', 'alipay_user_id' => '2088xxx'], true)
        ];

        if ($product->trans_currency) $query['trans_currency'] = $product->trans_currency;
        if ($product->settle_currency) $query['settle_currency'] = $product->settle_currency;

        if (config('services.alipay.logon_id')) {
            $query['payee_logon_id'] = config('services.alipay.logon_id');
        }

        $response = app('alipay')->qrCode($query);
        if ($response->code == 10000) {
            // 写本地授权码订单记录
            $product->authorizationCodeOrders()->create([
                'order_no' => $orderNo,
                'request_no' => $requestNo,
                'extra' => json_encode($query, true),
                'price' => $product->price,
                'user_id' => Admin::user()->id,
                'status' => true
            ]);

            return $response->code_url;
        } else {
            // 写本地授权码订单记录
            $product->authorizationCodeOrders()->create([
                'order_no' => $orderNo,
                'request_no' => $requestNo,
                'price' => $product->price,
                'user_id' => Admin::user()->id,
                'status' => false
            ]);

            return false;
        }
    }

    /**
     * 合成二维码图片
     *
     * @param $qrCodeImg
     * @param $backgroundImg
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function mergeImage($qrCodeImg, $backgroundImg)
    {
        // 合成图片
        $image = Image::make($backgroundImg)
            ->insert(
                Image::make($qrCodeImg)->resize(133, 135),
                'top-left',
                203,
                910
            );

        return $image->stream('jpg');
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
