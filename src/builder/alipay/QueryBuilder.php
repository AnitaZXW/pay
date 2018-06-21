<?php

namespace Apay\alipay\request;

class Query extends Common
{
    /**
     * 支付结果查询
     */
	private $bizContent;

    //订单详情
    private $tradeNo;
    private $outTradeNo;

    public function __construct($config)
    {
        parent::__construct($config);
    }
    
    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    }

    public function getBizContent()
    {
        $params = array(
            'out_trade_no' => $this->outTradeNo,
        );

        $this->bizContent = json_encode($params);

        return $this->bizContent;
    }

    public function getApiMethodName()
    {
        return 'alipay.trade.query';
    }

    public function getParams()
    {
        $params["app_id"] = $this->app_id;
        $params["method"] = $this->getApiMethodName();
        $params["version"] = $this->version;
        $params["format"] = $this->format;
        $params["sign_type"] = $this->sign_type;
        $params["timestamp"] = date("Y-m-d H:i:s");
        $params["charset"] = $this->charset;
        $params['biz_content'] = $this->getBizContent();
        
        $params["sign"] = $this->generateSign($params, $this->sign_type);
        return $params;
    }
}
