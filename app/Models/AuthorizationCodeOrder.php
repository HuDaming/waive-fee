<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
