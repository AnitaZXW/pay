<?php

namespace Apay\alipay\request;

class Common {

	public $app_id;

	public $rsa_private_key;

	public $rsa_public_key;

	public $gateway_url = "https://openapi.alipay.com/gateway.do";
	
	public $format = "json";
	
	public $version = "1.0";

	public $charset = "utf-8";

	public $sign_type = "RSA2";

	public $notify_url;

	public $return_url;


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
}