<?php

namespace Apay\alipay\request;

class Query extends Common
{
    /**
     * 支付结果查询
     */
	private $bizContent;

    //订单详情
    private $trade_no;
    private $out_trade_no;

    private $method = "alipay.trade.query";

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
        $parameter = array(
            'out_trade_no' => $this->out_trade_no,
            'trade_no' => $this->trade_no
        );

        $this->bizContent = json_encode($parameter);

        return $this->bizContent;
    }
    
}
