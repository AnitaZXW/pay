<?php 

namespace Apay;

use Apay\alipay\aop\Request\AlipayTradeWapPayRequest;
use Apay\alipay\aop\Request\AlipayTradeQueryRequest;
use Apay\alipay\aop\AopClient;
use Exception;

class Alipay
{
	public $aop;

	public function __construct($config)
	{
		$aop = new AopClient();

		if (!empty($config['gateway_url'])) {
			$aop->gatewayUrl = $config['gateway_url'];
		}

		if (!empty($config['sign_type'])) {
			$aop->signType =  $config['sign_type'];
		}

		if (!empty($config['charset'])) {
			$aop->postCharset =  $config['charset'];
		}

		$aop->appId =  $config['app_id'];
		$aop->rsaPrivateKey =  $config['private_key'];
		$aop->alipayrsaPublicKey =  $config['alipay_public_key'];
		$aop->notify_url =  $config['notify_url'];
		$aop->return_url =  $config['return_url'];

		
		if(empty($aop->appId)||trim($aop->appId)==""){
			throw new Exception("appid should not be NULL!");
		}

		if(empty($aop->rsaPrivateKey)||trim($aop->rsaPrivateKey)==""){
			throw new Exception("private_key should not be NULL!");
		}

		if(empty($aop->alipayrsaPublicKey)||trim($aop->alipayrsaPublicKey)==""){
			throw new Exception("alipay_public_key should not be NULL!");
		}

		if(empty($aop->postCharset)||trim($aop->postCharset)==""){
			throw new Exception("charset should not be NULL!");
		}
		
		if(empty($aop->gatewayUrl)||trim($aop->gatewayUrl)==""){
			throw new Exception("gateway_url should not be NULL!");
		}

		if(empty($aop->signType)||trim($aop->signType)==""){
			throw new Exception("sign_type should not be NULL");
		}

		$this->aop = $aop;
	}

	public function wapPay($order)
	{
		$request = new AlipayTradeWapPayRequest();

    	$request->setSubject($order['subject']);
    	$request->setOutTradeNo($order['out_trade_no']);
    	$request->setTotalAmount($order['total_amount']);
    	$request->setProductCode($order['product_code']);

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
		$sysParams["charset"] = $this->aop->postCharset;
		$sysParams["biz_content"] = $request->getBizContent();

		//签名
		$sysParams["sign"] = $this->aop->generateSign($sysParams, $this->aop->signType);

		return $sysParams;
	}

	public function query($order)
	{
		$request = new AlipayTradeQueryRequest();
	    $request->setOutTradeNo($order['out_trade_no']);

		//组装系统参数
		$sysParams["app_id"] = $this->aop->appId;
		$sysParams["version"] = $this->aop->apiVersion;
		$sysParams["format"] = $this->aop->format;
		$sysParams["sign_type"] = $this->aop->signType;
		$sysParams["method"] = $request->getApiMethodName();
		$sysParams["timestamp"] = date("Y-m-d H:i:s");
		$sysParams["charset"] = $this->aop->postCharset;
		$sysParams["biz_content"] = $request->getBizContent();

		$sysParams["sign"] = $this->aop->generateSign($sysParams, $this->aop->signType);

		return $sysParams;
	}
}