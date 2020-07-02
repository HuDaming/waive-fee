<?php

namespace Utils\Alipay;

use App\Models\User;

require_once "alipay-sdk/aop/AopClient.php";
require_once "alipay-sdk/aop/request/AlipayUserInfoShareRequest.php";
require_once "alipay-sdk/aop/request/AlipaySystemOauthTokenRequest.php";
require_once "alipay-sdk/aop/request/AlipayFundAuthOrderAppFreezeRequest.php";
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

    /**
     * 获取 Access token
     *
     * @param $code
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function getAccessToken($code)
    {
        $request = new \AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($code);
        $result = $this->aop->execute($request);

        return $result->alipay_system_oauth_token_response;
    }

    /**
     * 获取用户数据
     *
     * @param $accessToken
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function getUserInfo($accessToken)
    {
        $request = new \AlipayUserInfoShareRequest();
        $result = $this->aop->execute($request, $accessToken);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        return $result->$responseNode;
    }

    public function authorizedFundsFreezeOrder(array $query)
    {
        $request = new \AlipayFundAuthOrderAppFreezeRequest();
        $request->setBizContent(json_encode($query, true));
        $result = $this->aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return $result->$responseNode;
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
