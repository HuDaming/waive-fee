<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\AuthorizationCodeOrder
 *
 * @property int $id
 * @property string $order_no 订单流水号
 * @property string $request_no 请求流水号
 * @property int $product_id
 * @property string|null $extra 订单详情
 * @property float $price 价格
 * @property int $user_id
 * @property int $status 获取授权码状态
 * @property string|null $message 授权失败原因
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AuthorizationCodeOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereRequestNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AuthorizationCodeOrder whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AuthorizationCodeOrder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AuthorizationCodeOrder withoutTrashed()
 * @mixin \Eloquent
 */
class AuthorizationCodeOrder extends Model
{
    use SoftDeletes;

    public $fillable = [
        'order_no', 'request_no', 'price', 'status', 'message', 'user_id', 'extra'
    ];

    /**
     * 获取有效的订单流水号
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function findAvailableOrderNo()
    {
        $prefix = date("YmdHis", time());
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
        $prefix = date("YmdHis", time());
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
