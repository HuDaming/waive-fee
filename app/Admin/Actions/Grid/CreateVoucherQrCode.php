<?php

namespace App\Admin\Actions\Grid;

use Image;
use QrCode;
use Dcat\Admin\Admin;
use App\Models\Product;
use Illuminate\Http\Request;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

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
        $userId = Admin::user()->id;

        // 获取二维码
        // $codeUrl = $this->getAlipayQrCode($product);
        // if (!$codeUrl) return $this->response()->error('授权二维码生成失败')->redirect('/products');
        $filename = md5(uniqid(rand(), true));
        $filePath = storage_path("qr_codes/{$filename}.png");
        QrCode::encoding('UTF-8')
            ->format('png')
            ->size(126)
            ->margin(3)
            ->generate(route('orders.create', [
                'product_id' => $product->id,
                'seller_id' => $userId
            ]), $filePath);

        // 合成二维码和背景图
        $file = $this->mergeImage($filePath, $product->full_background_img_url);

        // try {
        // 上传到七牛空间
        $disk = \Storage::disk('qiniu');
        $path = "qr_code/{$product->id}_{$userId}/" . $filename . '.jpg';
        $result = $disk->put($path, $file);
        if ($result) {
            // 保存用户这个商品二维码
            $product->users()->sync([$userId => ['qr_code' => $path]], false);
        } else {
            return $this->response()->error('二维码上传失败')->redirect('/products');
        }

        // } catch (\Exception $e) {
        //     return $this->response()->error('二维码保存失败' . $e->getMessage())->redirect('/products');
        // }

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
}
