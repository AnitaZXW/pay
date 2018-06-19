<?php

namespace Apay\alipay\request;

class Common {

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

    public function execute()
    {
    	$sysParams = $this->getParams();
		//系统参数放入GET请求串
		$requestUrl = $this->gatewayUrl . "?";
		foreach ($sysParams as $sysParamKey => $sysParamValue) {
			$requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->charset)) . "&";
		}
		$requestUrl = substr($requestUrl, 0, -1);

		try {
			$resp = $this->curl($requestUrl);
		} catch (Exception $e) {
			//记录日志
			return false;
		}

		// 将返回结果转换本地文件编码
		$r = iconv($this->charset, $this->charset . "//IGNORE", $resp);

		$signData = null;

		if ("json" == $this->format) {
			$respObject = json_decode($r);
			if (null !== $respObject) {
				$signData = $this->parserJSONSignData($request, $resp, $respObject);

				var_dump($signData);die;
			}
		}

		// 验签
		$this->checkResponseSign($request, $signData, $resp, $respObject);

		return $respObject;
	}


	function parserResponseSubCode($request, $responseContent, $respObject, $format) {

		if ("json" == $format) {

			$apiName = $request->getApiMethodName();
			$rootNodeName = str_replace(".", "_", $apiName) . $this->RESPONSE_SUFFIX;
			$errorNodeName = $this->ERROR_RESPONSE;

			$rootIndex = strpos($responseContent, $rootNodeName);
			$errorIndex = strpos($responseContent, $errorNodeName);

			if ($rootIndex > 0) {
				// 内部节点对象
				$rInnerObject = $respObject->$rootNodeName;
			} elseif ($errorIndex > 0) {

				$rInnerObject = $respObject->$errorNodeName;
			} else {
				return null;
			}

			// 存在属性则返回对应值
			if (isset($rInnerObject->sub_code)) {

				return $rInnerObject->sub_code;
			} else {

				return null;
			}


		} elseif ("xml" == $format) {

			// xml格式sub_code在同一层级
			return $respObject->sub_code;

		}


	}
	
	public function checkResponseSign($request, $signData, $resp, $respObject) {

		if (!$this->checkEmpty($this->rsa_public_key)) {


			if ($signData == null || $this->checkEmpty($signData->sign) || $this->checkEmpty($signData->signSourceData)) {

				throw new Exception(" check sign Fail! The reason : signData is Empty");
			}


			// 获取结果sub_code
			$responseSubCode = $this->parserResponseSubCode($request, $resp, $respObject, $this->format);


			if (!$this->checkEmpty($responseSubCode) || ($this->checkEmpty($responseSubCode) && !$this->checkEmpty($signData->sign))) {

				$checkResult = $this->verify($signData->signSourceData, $signData->sign, $this->alipayPublicKey, $this->signType);


				if (!$checkResult) {

					if (strpos($signData->signSourceData, "\\/") > 0) {

						$signData->signSourceData = str_replace("\\/", "/", $signData->signSourceData);

						$checkResult = $this->verify($signData->signSourceData, $signData->sign, $this->alipayPublicKey, $this->signType);

						if (!$checkResult) {
							throw new Exception("check sign Fail! [sign=" . $signData->sign . ", signSourceData=" . $signData->signSourceData . "]");
						}

					} else {

						throw new Exception("check sign Fail! [sign=" . $signData->sign . ", signSourceData=" . $signData->signSourceData . "]");
					}

				}
			}


		}
	}

	function parserJSONSign($responseJSon) {

		return $responseJSon->sign;
	}

	function parserJSONSignData($request, $responseContent, $responseJSON) {

		$signData = new SignData();

		$signData->sign = $this->parserJSONSign($responseJSON);
		$signData->signSourceData = $this->parserJSONSignSource($request, $responseContent);


		return $signData;

	}

	function parserJSONSignSource($request, $responseContent) {

		$apiName = $request->getApiMethodName();
		$rootNodeName = str_replace(".", "_", $apiName) . $this->RESPONSE_SUFFIX;

		$rootIndex = strpos($responseContent, $rootNodeName);
		$errorIndex = strpos($responseContent, $this->ERROR_RESPONSE);


		if ($rootIndex > 0) {

			return $this->parserJSONSource($responseContent, $rootNodeName, $rootIndex);
		} else if ($errorIndex > 0) {

			return $this->parserJSONSource($responseContent, $this->ERROR_RESPONSE, $errorIndex);
		} else {

			return null;
		}


	}

	function parserJSONSource($responseContent, $nodeName, $nodeIndex) {
		$signDataStartIndex = $nodeIndex + strlen($nodeName) + 2;
		$signIndex = strpos($responseContent, "\"" . $this->SIGN_NODE_NAME . "\"");
		// 签名前-逗号
		$signDataEndIndex = $signIndex - 1;
		$indexLen = $signDataEndIndex - $signDataStartIndex;
		if ($indexLen < 0) {

			return null;
		}

		return substr($responseContent, $signDataStartIndex, $indexLen);
	}

	private function curl($url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_HEADER, FALSE); // 过滤HTTP头
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
		// curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); //证书地址
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		if ($responseText) {
			curl_close($curl);
			return $responseText;
		} else {
			$error = curl_error($curl);
			//将error写入log
			curl_close($curl);
			return false;
		}
	}
}