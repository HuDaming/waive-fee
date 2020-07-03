<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order query()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order withoutTrashed()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use SoftDeletes, HasDateTimeFormatter;

    // 销售产品码
    const CODE_PRE_AUTH = 'PRE_AUTH_ONLINE';
    const CODE_OVERSEAS = 'OVERSEAS_INSTORE_AUTH';

    public static $codeMap = [
        self::CODE_PRE_AUTH => '国内预授权产品',
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

    const STATUS_PENDING = 'pending';
    const STATUS_FREEZE = 'freeze';
    const STATUS_UNFREEZE = 'unfreeze';
    const STATUS_DEFAULT = 'default';

    public static $statusMap = [
        self::STATUS_PENDING => '待处理',
        self::STATUS_FREEZE => '冻结',
        self::STATUS_UNFREEZE => '已解冻',
        self::STATUS_DEFAULT => '已违约',
    ];

    protected $fillable = [
        'order_no', 'request_no',
        'order_title', 'amount', 'product_code', 'payee_logon_id', 'payee_user_id', 'pay_timeout', 'scene_code', 'trans_currency', 'settle_currency', 'enable_pay_channels', 'identity_params',
        'contact_name', 'contact_mobile', 'address', 'remark',
        'status', 'user_id', 'seller_id', 'product_id'
    ];

    /**
     * 获取有效的订单流水号
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function findAvailableOrderNo()
    {
        $prefix = date("YmdHis");
        for ($i = 0; $i < 10; $i++) {
            $no = $prefix . str_pad(random_int(0, 999999), 6, 0, STR_PAD_LEFT);
            if (!static::query()->where('order_no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('find order no failed');

        return false;
    }

    /**
     * 获取有效的请求流水号
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function findAvailableRequestNo()
    {
        $prefix = date("YmdHis");
        for ($i = 0; $i < 10; $i++) {
            $no = $prefix . str_pad(random_int(0, 999999), 6, 0, STR_PAD_LEFT);
            if (!static::query()->where('request_no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('find request no failed');

        return false;
    }
}
