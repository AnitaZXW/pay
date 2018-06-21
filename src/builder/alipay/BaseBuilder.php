<?php

namespace Apay\builder\alipay;

use Apay\util\Curl;
use Apay\util\DataParse;
use Apay\config\AlipayConfig;

class BaseBuilder
{
	protected $app_id;
	protected $format = 'json';
	protected $notify_url;
	protected $return_url;
	protected $charset = 'utf-8';
	protected $sign_type = 'RSA2';
	protected $version = '1.0';

	protected $private_key;
	protected $public_key;
	protected $bizContent;

	private $RESPONSE_SUFFIX = "_response";
	private $ERROR_RESPONSE = "error_response";
	private $SIGN_NODE_NAME = "sign";

	public function __construct($commonConfig)
	{	
		foreach ($commonConfig as $k => $v) {

			switch ($k) {
				case 'app_id':
					$this->app_id = $v;
					break;
				case 'return_url':
					$this->return_url = $v;
					break;
				case 'notify_url':
					$this->notify_url = $v;
					break;
				case 'sign_type':
					$this->sign_type = $v;
				case 'charset':
					$this->charset = $v;
				case 'public_key':
					$this->public_key = $v;
					break;
				case 'private_key':
					$this->private_key = $v;
					break;
				default:
					break;
			}
		}
	}

	protected function filter($params)
	{	
		foreach ($params as $k => $v) {
			if (empty($v)) {
				unset($params[$k]);
			}
		}

		return $params;
	}

	public function generateSign($params) {
		return $this->sign(DataParse::ToUrlParams($params));
	}

	protected function sign($data)
	{
		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($this->private_key, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";

		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置'); 

		if ("RSA2" == $data['sign_type']) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}

		$sign = base64_encode($sign);
		return $sign;
	}

 //    public function run()
 //    {
 //    	$sysParams = $this->getParams();
	// 	//系统参数放入GET请求串
	// 	$requestUrl = $this->gatewayUrl . "?";
	// 	foreach ($sysParams as $sysParamKey => $sysParamValue) {
	// 		$requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->charset)) . "&";
	// 	}
	// 	$requestUrl = substr($requestUrl, 0, -1);

	// 	try {
	// 		$resp = Curl::curlPost($requestUrl);
	// 	} catch (Exception $e) {
	// 		//记录日志
	// 		return false;
	// 	}

	// 	//将返回结果转换本地文件编码
	// 	$r = iconv($this->charset, $this->charset . "//IGNORE", $resp);

	// 	if ("json" == $this->format) {
	// 		$respObject = json_decode($r, true);
	// 	}

	// 	return $respObject;
	// }

	// public function verify($data, $sign, $rsaPublicKeyFilePath, $signType = 'RSA') {

	// 	if(!$this->checkEmpty($this->rsa_public_key)){

	// 		$pubKey= $this->rsa_public_key;
	// 		$res = "-----BEGIN PUBLIC KEY-----\n" .
	// 			wordwrap($pubKey, 64, "\n", true) .
	// 			"\n-----END PUBLIC KEY-----";
	// 	}else {
	// 		//读取公钥文件
	// 		$pubKey = file_get_contents($rsaPublicKeyFilePath);
	// 		//转换为openssl格式密钥
	// 		$res = openssl_get_publickey($pubKey);
	// 	}
	// 	($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');  
	// 	//调用openssl内置方法验签，返回bool值
	// 		$data = $this->getSignContent(json_decode($data, true));

	// 	if ("RSA2" == $signType) {
	// 		// echo "\n";
	// 		// var_dump($data);
	// 		// echo "\n";
	// 		// var_dump($sign);
	// 		// echo "\n";
	// 		// var_dump($res);
	// 		$result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
	// 	} else {
	// 		$result = (bool)openssl_verify($data, base64_decode($sign), $res);
	// 	}

	// 	if($this->checkEmpty($this->rsa_public_key)) {
	// 		//释放资源
	// 		openssl_free_key($res);
	// 	}

	// 	return $result;
	// }

	public function buildParams($order)
	{
		$order = $this->filter($order);
		$urlParams = [
			'app_id' => $this->app_id,
			'method' => $this->getApiMethodName(),
			'format' => $this->format,
			'charset' => $this->charset,
			'sign_type' => $this->sign_type,
			'timestamp' => date('Y-m-d H:i:s'),
			'version' => $this->version,
			'return_url' => $this->return_url,
			'notify_url' => $this->app_id,
		];
		
		$urlParams['biz_content'] = json_encode($order);
		$urlParams['sign'] = $this->generateSign($urlParams);

		return $this->filter($urlParams);
	}
}