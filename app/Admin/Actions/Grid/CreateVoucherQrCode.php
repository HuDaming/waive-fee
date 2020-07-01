<?php

namespace App\Admin\Actions\Grid;

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
        $channels = json_encode([
            ['payChannelType' => 'PCREDIT_PAY'],
            ['payChannelType' => 'MONEY_FUND'],
        ], true);
        $extraParam = json_encode(['category' => 'HOME'], true);
        $identityParams = json_encode([
            'identity_hash' => 'ABCDEFDxxxxxx',
            'alipay_user_id' => '2088xxx'
        ], true);
        $query = [
            'out_order_no' => '8077735255938026',
            'out_request_no' => '8077735255938037',
            'order_title' => '预授权发码',
            'amount' => 100.00,
            'payee_user_id' => '2088102181099210',
            // 'payee_logon_id' => '159****5620',
            'pay_timeout' => '2d',
            'extra_param' => $extraParam,
            'product_code' => 'PRE_AUTH',
            'trans_currency' => 'USD',
            'settle_currency' => 'USD',
            'enable_pay_channels' => $channels,
            'identity_params' => $identityParams
        ];

        $response = app('alipay')->qrCode($query);
        if ($response->code == 10000)
            return $response->code_url;

        return false;
    }

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
}
