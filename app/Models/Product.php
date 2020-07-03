<?php

namespace App\Models;

use App\Models\AuthorizationCodeOrder;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $code 预售产品码
 * @property string $name 产品名称
 * @property string|null $intro 产品简介
 * @property string|null $banner_img 头图
 * @property string|null $background_img 二维码背景图
 * @property float $price 价格
 * @property string|null $trans_currency 标价币种
 * @property string|null $settle_currency 结算币种
 * @property int $pay_timeout 最晚履约时间
 * @property array $enable_pay_channels 可用支付渠道
 * @property bool $on_sale 是否上架
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AuthorizationCodeOrder[] $authorizationCodeOrders
 * @property-read int|null $authorization_code_orders_count
 * @property-read mixed $full_background_img_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\Dcat\Admin\Models\Administrator[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereBackgroundImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereBannerImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereEnablePayChannels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereOnSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product wherePayTimeout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSettleCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereTransCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use SoftDeletes, HasDateTimeFormatter;

    // 销售产品码
    const CODE_PRE = 'PRE_AUTH';
    const CODE_OVERSEAS = 'OVERSEAS_INSTORE_AUTH';

    public static $codeMap = [
        self::CODE_PRE => '国内预授权产品',
        self::CODE_OVERSEAS => '境外预授权产品'
    ];

    // 标价币种
    const TRANS_CURRENCY_AUD = 'AUD';
    const TRANS_CURRENCY_NZD = 'NZD';
    const TRANS_CURRENCY_TWD = 'TWD';
    const TRANS_CURRENCY_USD = 'USD';
    const TRANS_CURRENCY_EUR = 'EUR';
    const TRANS_CURRENCY_GBP = 'GBP';

    public static $transCurrencyMap = [
        self::TRANS_CURRENCY_AUD => '澳元',
        self::TRANS_CURRENCY_NZD => '新西兰元',
        self::TRANS_CURRENCY_TWD => '台币',
        self::TRANS_CURRENCY_USD => '美元',
        self::TRANS_CURRENCY_EUR => '欧元',
        self::TRANS_CURRENCY_GBP => '英镑',
    ];

    // 结算币种
    const SETTLE_CURRENCY_AUD = 'AUD';
    const SETTLE_CURRENCY_NZD = 'NZD';
    const SETTLE_CURRENCY_TWD = 'TWD';
    const SETTLE_CURRENCY_USD = 'USD';
    const SETTLE_CURRENCY_EUR = 'EUR';
    const SETTLE_CURRENCY_GBP = 'GBP';

    public static $settleCurrencyMap = [
        self::SETTLE_CURRENCY_AUD => '澳元',
        self::SETTLE_CURRENCY_NZD => '新西兰元',
        self::SETTLE_CURRENCY_TWD => '台币',
        self::SETTLE_CURRENCY_USD => '美元',
        self::SETTLE_CURRENCY_EUR => '欧元',
        self::SETTLE_CURRENCY_GBP => '英镑',
    ];

    // 可用支付渠道
    const CHANNEL_MONEY_FUND = 'MONEY_FUND';
    const CHANNEL_PCREDIT_PAY = 'PCREDIT_PAY';
    const CHANNEL_CREDITZHIMA = 'CREDITZHIMA';

    public static $channelMap = [
        self::CHANNEL_MONEY_FUND => '余额宝',
        self::CHANNEL_PCREDIT_PAY => '花呗',
        self::CHANNEL_CREDITZHIMA => '芝麻信用',
    ];

    public $fillable = [
        'code', 'name', 'intro', 'pay_timeout', 'banner_img', 'background_img',
        'trans_currency', 'settle_currency', 'enable_pay_channels', 'price',
        'on_sale'
    ];

    protected $casts = [
        'on_sale' => 'boolean',
        'enable_pay_channels' => 'array',
    ];

    public function getFullBackgroundImgUrlAttribute()
    {
        return Str::startsWith('http', $this->background_img) ?
            $this->background_img :
            config('filesystems.disks.qiniu.url') . $this->background_img;
    }

    public function getBannerImgAttribute($value)
    {
        return $value ?: 'images/default_banner.jpg';
    }

    public function getBackgroundImgAttribute($value)
    {
        return $value ?: 'images/default_background.jpg';
    }

    public function getPriceAttribute($value)
    {
        return sprintf("%.2f", $value);
    }

    /**
     * 用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Administrator::class, 'product_user', 'product_id', 'user_id')
            ->withPivot('qr_code')
            ->withTimestamps();
    }

    // 关联的授权码订单
    public function authorizationCodeOrders()
    {
        return $this->hasMany(AuthorizationCodeOrder::class);
    }

    public static function getPayTimeoutDays()
    {
        $days = [];
        for ($i = 1; $i <= 15; $i++) {
            $days[$i] = "{$i} 天";
        }

        return $days;
    }
}
