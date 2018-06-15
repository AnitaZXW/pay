<?php

namespace Apay\alipay\aop\request;

class AlipayTradeWapPayRequest
{
	/** 
	 * 手机网站支付接口2.0
	 **/
	private $bizContent;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $apiVersion="1.0";
    private $needEncrypt=false;

    private $body;
    private $subject;
    private $outTradeNo;
    private $timeExpress;
    private $totalAmount;
    private $sellerId;
    private $productCode = 'QUICK_WAP_PAY';
    private $bizContentarr = array();

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        $this->bizContentarr['body'] = $body;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->bizContentarr['subject'] = $subject;
    }

    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        $this->bizContentarr['out_trade_no'] = $outTradeNo;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        $this->bizContentarr['total_amount'] = $totalAmount;
    }

    public function getProdCode()
    {
        return $this->productCode;
    }

    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
        $this->bizContentarr['product_code'] = $productCode;
    }

    public function setTimeExpress($timeExpress)
    {
        $this->timeExpress = $timeExpress;
        $this->bizContentarr['timeout_express'] = $timeExpress;
    }

    public function getTimeExpress()
    {
        return $this->timeExpress;
    }

    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;
        $this->bizContentarr['seller_id'] = $sellerId;
    }

    public function getSellerId()
    {
        return $this->sellerId;
    }

    public function getBizContent()
    {
        if(!empty($this->bizContentarr)){
            $this->bizContent = json_encode($this->bizContentarr,JSON_UNESCAPED_UNICODE);
        }
        return $this->bizContent;
    }

	public function setBizContent($bizContent)
	{
		$this->bizContent = $bizContent;
		$this->apiParas["biz_content"] = $bizContent;
	}

	public function getApiMethodName()
	{
		return "alipay.trade.wap.pay";
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function setNeedEncrypt($needEncrypt)
	{
		$this->needEncrypt=$needEncrypt;
	}

	public function getNeedEncrypt()
	{
		return $this->needEncrypt;
	} 

}
