<?php

namespace Utils\Alipay;

use AlipaySdk\aop\AopClient;
use AlipaySdk\aop\request\AlipayTradePayRequest;
use AlipaySdk\aop\request\AlipayTradeRefundRequest;
use AlipaySdk\aop\request\AlipayUserInfoShareRequest;
use AlipaySdk\aop\request\AlipaySystemOauthTokenRequest;
use AlipaySdk\aop\request\ZhimaAuthInfoAuthqueryRequest;
use AlipaySdk\aop\request\AlipayFundAuthOrderUnfreezeRequest;
use AlipaySdk\aop\request\AlipayFundAuthOrderAppFreezeRequest;
use AlipaySdk\aop\request\AlipayFundAuthOperationCancelRequest;
use AlipaySdk\aop\request\AlipayFundAuthOrderVoucherCreateRequest;
use AlipaySdk\aop\request\AlipayFundAuthOperationDetailQueryRequest;

class Alipay implements AlipayContract
{
    protected $appId;

    protected $baseUri;

    protected $alipayPublicKey;

    protected $appPrivateKey;

    protected $aop;

    public function __construct()
    {
        $config = config('services.alipay');

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
        $request = new AlipaySystemOauthTokenRequest();
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
        $request = new AlipayUserInfoShareRequest();
        $result = $this->aop->execute($request, $accessToken);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        return $result->$responseNode;
    }

    /**
     * 资金冻结授权
     *
     * @param array $query
     * @return \SimpleXMLElement
     * @throws \Exception、
     */
    public function fundAuthOrderAppFreeze(array $query)
    {
        $request = new AlipayFundAuthOrderAppFreezeRequest();

        return $this->response($request, $query);
    }

    /**
     * 统一收单交易支付
     *
     * @param array $query
     * @return \SimpleXMLElement
     */
    public function tradePay(array $query = [])
    {
        $request = new AlipayTradePayRequest();

        return $this->response($request, $query);
    }

    /**
     * 资金授权撤销接口
     *
     * @param array $query
     * @return \SimpleXMLElement
     */
    public function fundAuthOperationCancel(array $query = [])
    {
        $request = new AlipayFundAuthOperationCancelRequest();

        return $this->response($request, $query);
    }

    /**
     * 资金授权解冻
     *
     * @param array $query
     * @return \SimpleXMLElement
     */
    public function fundAuthOrderUnfreeze(array $query = [])
    {
        $request = new AlipayFundAuthOrderUnfreezeRequest($query);

        return $this->response($request, $query);
    }

    /**
     * 资金授权操作查询
     *
     * @param $outOrderNo
     * @param $outRequestNo
     * @return \SimpleXMLElement
     */
    public function fundAuthOperationDetailQuery($outOrderNo, $outRequestNo)
    {
        $request = new AlipayFundAuthOperationDetailQueryRequest();

        return $this->response($request, ['out_order_no' => $outOrderNo, 'out_request_no' => $outRequestNo]);
    }

    /**
     * 统一收单交易退款
     *
     * @param array $query
     * @return \SimpleXMLElement
     */
    public function tradeRefund(array $query = [])
    {
        $request = new AlipayTradeRefundRequest();

        return $this->response($request, $query);
    }

    /**
     * 统一收单线下交易查询
     *
     * @param $outTradeNo
     * @return \SimpleXMLElement
     */
    public function tradeQuery($outTradeNo)
    {
        $request = new \AlipayTradeQueryRequest();

        return $this->response($request, ['out_trade_no' => $outTradeNo]);
    }

    /**
     * 查询芝麻信用授权
     *
     * @param array $query
     * @return \SimpleXMLElement
     */
    public function zhimaAuthInfoQuery(array $query = [])
    {
        $request = new ZhimaAuthInfoAuthqueryRequest();

        return $this->response($request, $query);
    }

    /**
     * 资金冻结授权发码
     *
     * @param array $query
     * @return mixed|\SimpleXMLElement
     * @throws \Exception
     */
    public function qrCode(array $query = [])
    {
        $request = new AlipayFundAuthOrderVoucherCreateRequest();

        return $this->response($request, $query);
    }

    /**
     *
     * @param $request
     * @param array $query
     * @return \SimpleXMLElement
     */
    protected function response($request, array $query = [])
    {
        $request->setBizContent(json_encode($query, true));

        $result = $this->aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        return $result->$responseNode;
    }

    protected function getAop()
    {
        $aop = new AopClient();
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
