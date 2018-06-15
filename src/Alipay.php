<?php 

namespace Apay;

use Apay\alipay\aop\Request\AlipayTradeWapPayRequest;
use Apay\alipay\aop\AopClient;

class Alipay
{
	public $aop;

	public function __construct($config)
	{
		$aop = new AopClient();

		if (!empty($config['gateway_url'])) {
			$aop->gatewayUrl = $config['gateway_url'];
		}

		if (!empty($config['private_key'])) {
			$aop->rsaPrivateKey =  $config['private_key'];
		}

		if (!empty($config['app_id'])) {
			$aop->appId =  $config['app_id'];
		}
		
		if (!empty($config['alipay_public_key'])) {
			$aop->alipayrsaPublicKey =  $config['alipay_public_key'];
		}
		
		if (!empty($config['sign_type'])) {
			$aop->signType =  $config['sign_type'];
		}

		if (!empty($config['notify_url'])) {
			$aop->notify_url =  $config['notify_url'];
		}

		if (!empty($config['return_url'])) {
			$aop->return_url =  $config['return_url'];
		}

		$this->aop = $aop;
	}

	public function wapPay($order)
	{
		$request = new AlipayTradeWapPayRequest();

		if (!empty($order['subject'])) {
	    	$request->setSubject($subject);
		}

		if (!empty($order['out_trade_no'])) {
	    	$request->setOutTradeNo($out_trade_no);
		}

		if (!empty($order['total_amount'])) {
	    	$request->setOutTradeNo($total_amount);
		}

		//组装系统参数
		$sysParams["app_id"] = $this->aop->appId;
		$sysParams["version"] = $this->aop->apiVersion;
		$sysParams["format"] = $this->aop->format;
		$sysParams["sign_type"] = $this->aop->signType;
		$sysParams["method"] = $request->getApiMethodName();
		$sysParams["timestamp"] = date("Y-m-d H:i:s");
		$sysParams["product_code"] = $request->getProdCode();
		$sysParams["notify_url"] = $this->aop->notify_url;
		$sysParams["return_url"] = $this->aop->return_url;
		$sysParams["charset"] = $this->postCharset;
		$sysParams["biz_content"] = $request->getBizContent();

		//签名
		$sysParams["sign"] = $this->aop->generateSign($sysParams, $this->signType);

		return $sysParams;
	}
}