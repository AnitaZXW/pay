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
	protected $gateway_url = 'https://openapi.alipay.com/gateway.do';

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
				case 'gateway_url':
					$this->gateway_url = $v;
					break;
				default:
					break;
			}
		}
	}

	public function buildParams($order)
	{
		$order = $this->filter($order);
		$urlParams = [
			'app_id' => $this->app_id,
			'method' => $this->getApiMethodName(),
			'format' => $this->format,
			'charset' => $this->charset,
			'timestamp' => date('Y-m-d H:i:s'),
			'sign_type' => $this->sign_type,
			// 'return_url' => $this->return_url,
			// 'notify_url' => $this->app_id,
			'version' => $this->version,
		];
		
		$urlParams['biz_content'] = json_encode($order);
		$urlParams['sign'] = $this->generateSign($urlParams);

		return $this->filter($urlParams);
	}

	public function run($order)
    {
    	$urlParams = $this->buildParams($order);
		$requestUrl = $this->gateway_url . "?".DataParse::ToUrlencodeParams($urlParams);
		try {
			$resp = Curl::curlGet($requestUrl);
		} catch (\Exception $e) {
			//记录日志
			return false;
		}

		//将返回结果转换本地文件编码
		$r = iconv($this->charset, $this->charset . "//IGNORE", $resp);

		if ("json" == $this->format) {
			$response = json_decode($r, true);
		}

		foreach ($response as $key => $value) {
			if ($key != 'sign') {
				$response['params'] = $value;
				unset($response[$key]);break;
			}
		}

		return $response['params'];
	}

	public function verify($data, $sign)
	{
		$res = "-----BEGIN PUBLIC KEY-----\n" .
			wordwrap($this->public_key, 64, "\n", true) .
			"\n-----END PUBLIC KEY-----";

		($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确'); 

		$toVerify = DataParse::ToUrlParams($data);
		if ("RSA2" == $this->sign_type) {
			$result = (bool)openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		} else {
			$result = (bool)openssl_verify($toVerify, base64_decode($sign), $res);
		}

		if(!$this->checkEmpty($this->public_key)) {
			//释放资源
			openssl_free_key($res);
		}

		return $result;
	}


	public function generateSign($params) {
		return $this->sign(DataParse::ToUrlParams($params), $params['sign_type']);
	}

	protected function sign($data, $sign_type = 'RSA2')
	{
		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($this->private_key, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";

		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置'); 

		if ("RSA2" == $sign_type) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}

		$sign = base64_encode($sign);
		return $sign;
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
}