<?php

namespace Apay\alipay\aop\request;

class AlipayTradeWapPayRequest
{
	/** 
	 * 手机网站支付接口2.0
	 **/
	private $bizContent;
    private $body;
    private $subject;
    private $outTradeNo;
    private $timeExpress;
    private $totalAmount;
    private $sellerId;
    private $productCode = 'QUICK_WAP_PAY';

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    public function getProdCode()
    {
        return $this->productCode;
    }

    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
    }

    public function setTimeExpress($timeExpress)
    {
        $this->timeExpress = $timeExpress;
    }

    public function getTimeExpress()
    {
        return $this->timeExpress;
    }

    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;
    }

    public function getSellerId()
    {
        return $this->sellerId;
    }

    public function getBizContent()
    {
        $parameter = array(
            'body' => $this->body,
            'subject' => $this->subject,
            'out_trade_no' => $this->out_trade_no,
            'total_amount' => $this->total_amount,
            'product_code' => $this->product_code,
        );

        $this->bizContent = json_encode($parameter);

        return $this->bizContent;
    }

	public function getApiMethodName()
	{
		return "alipay.trade.wap.pay";
	}
}
