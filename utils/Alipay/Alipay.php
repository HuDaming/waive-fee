<?php

namespace Utils\Alipay;

require_once "alipay-sdk/aop/AopClient.php";
require_once "alipay-sdk/aop/request/AlipayFundAuthOrderVoucherCreateRequest.php";

class Alipay implements AlipayContract
{
    protected $appId;

    protected $baseUri;

    protected $alipayPublicKey;

    protected $appPrivateKey;

    protected $aop;

    public function __construct(array $config)
    {
        $this->appId = $config['app_id'];
        $this->baseUri = $config['base_uri'];
        $this->alipayPublicKey = $config['alipay_public_key'];
        $this->appPrivateKey = $config['app_private_key'];

        $this->aop = $this->getAop();
    }

    public function qrCode(array $query = [])
    {
        $request = new \AlipayFundAuthOrderVoucherCreateRequest();
        $request->setBizContent(json_encode($query, true));

        $result = $this->aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return $result->$responseNode;
    }

    protected function getAop()
    {
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->baseUri;
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->appPrivateKey;
        $aop->alipayrsaPublicKey = $this->alipayPublicKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';

        return $aop;
    }
}
