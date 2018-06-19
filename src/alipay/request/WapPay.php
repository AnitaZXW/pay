<?php

namespace Apay\alipay\request;

class WapPay extends Common
{
    private $body;
    private $subject;
    private $outTradeNo;
    private $timeExpress;
    private $totalAmount;
    private $sellerId;
    private $productCode = 'QUICK_WAP_PAY';

    private $bizContent;
    private $method = 'alipay.trade.wap.pay';

    public function __construct($config)
    {
        parent::__construct($config);
    }

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
        $params = array(
            'body' => $this->body,
            'subject' => $this->subject,
            'out_trade_no' => $this->outTradeNo,
            'total_amount' => $this->totalAmount,
            'product_code' => $this->productCode,
        );

        $this->bizContent = json_encode($params);

        return $this->bizContent;
    }

    public function getPayParams()
    {
        //组装系统参数
        $params["app_id"] = $this->app_id;
        $params["method"] = $this->method;
        $params["version"] = $this->version;
        $params["format"] = $this->format;
        $params["sign_type"] = $this->sign_type;
        $params["timestamp"] = date("Y-m-d H:i:s");
        $params["notify_url"] = $this->notify_url;
        $params["return_url"] = $this->return_url;
        $params["charset"] = $this->charset;
        $params['biz_content'] = $this->getBizContent();
        
        $params["sign"] = $this->generateSign($params, $this->sign_type);

        return $params;
    }
}
