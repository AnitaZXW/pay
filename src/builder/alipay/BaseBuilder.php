<?php

namespace Apay\builder\alipay;

use Apay\util\Curl;


class BaseBuilder {

	public $app_id;

	public $rsa_private_key;

	public $rsa_public_key;

	public $gatewayUrl = "https://openapi.alipay.com/gateway.do";
	
	public $format = "json";
	
	public $version = "1.0";

	public $charset = "utf-8";

	public $sign_type = "RSA2";

	public $notify_url;

	public $return_url;

	private $RESPONSE_SUFFIX = "_response";

	private $ERROR_RESPONSE = "error_response";

	private $SIGN_NODE_NAME = "sign";

	public function __construct($config)
    {
        $this->app_id = $config['app_id'];
        $this->notify_url = $config['notify_url'];
        $this->return_url = $config['return_url'];
        $this->rsa_public_key = $config['rsa_public_key'];
        $this->rsa_private_key = $config['rsa_private_key'];
        $this->version = $config['version'];
        $this->sign_type = $config['sign_type'];
    }

	//加密RSA2
	public function generateSign($params, $sign_type = "RSA2") {
		return $this->sign($this->getSignContent($params), $sign_type);
	}

	protected function getSignContent($params) {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

				// 转换成目标字符集
				$v = $this->characet($v, $this->charset);

				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i++;
			}
		}

		unset ($k, $v);
		return $stringToBeSigned;
	}

	protected function sign($data, $sign_type = "RSA2")
	{
		$priKey=$this->rsa_private_key;
			$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
				wordwrap($priKey, 64, "\n", true) .
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

	protected function checkEmpty($value) {
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (trim($value) === "")
			return true;

		return false;
	}

	function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

    public function run()
    {
    	$sysParams = $this->getParams();
		//系统参数放入GET请求串
		$requestUrl = $this->gatewayUrl . "?";
		foreach ($sysParams as $sysParamKey => $sysParamValue) {
			$requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->charset)) . "&";
		}
		$requestUrl = substr($requestUrl, 0, -1);

		try {
			$resp = Curl::curlPost($requestUrl);
		} catch (Exception $e) {
			//记录日志
			return false;
		}

		//将返回结果转换本地文件编码
		$r = iconv($this->charset, $this->charset . "//IGNORE", $resp);

		if ("json" == $this->format) {
			$respObject = json_decode($r, true);
		}

		return $respObject;
	}

	function verify($data, $sign, $rsaPublicKeyFilePath, $signType = 'RSA') {

		if(!$this->checkEmpty($this->rsa_public_key)){

			$pubKey= $this->rsa_public_key;
			$res = "-----BEGIN PUBLIC KEY-----\n" .
				wordwrap($pubKey, 64, "\n", true) .
				"\n-----END PUBLIC KEY-----";
		}else {
			//读取公钥文件
			$pubKey = file_get_contents($rsaPublicKeyFilePath);
			//转换为openssl格式密钥
			$res = openssl_get_publickey($pubKey);
		}
		($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');  
		//调用openssl内置方法验签，返回bool值
			$data = $this->getSignContent(json_decode($data, true));

		if ("RSA2" == $signType) {
			// echo "\n";
			// var_dump($data);
			// echo "\n";
			// var_dump($sign);
			// echo "\n";
			// var_dump($res);
			$result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		} else {
			$result = (bool)openssl_verify($data, base64_decode($sign), $res);
		}

		if($this->checkEmpty($this->rsa_public_key)) {
			//释放资源
			openssl_free_key($res);
		}

		return $result;
	}
}