<?php

namespace Utils\Alipay;

use Utils\Alipay\Alipay;

/**
 * Class Facade
 *
 * @package Utils\Alipay
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return Alipay::class;
    }
}
