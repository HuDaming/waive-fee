<?php

namespace Utils\Alipay;

interface AlipayContract
{
    /**
     * 生成二维码
     *
     * @param array $query 业务请求参数
     * @return mixed
     */
    public function qrCode(array $query = []);
}
