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

	//签名类型
	public $sign_type = "RSA2";

	//自加
	public $notify_url;

	public $return_url;

	//加密RSA2
	public function generateSign($params, $sign_type = "RSA2") {
		return $this->sign($this->getSignContent($params), $signType);
	}

	protected function getSignContent($params) {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

				// 转换成目标字符集
				$v = $this->characet($v, $this->postCharset);

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

	protected function sign($data, $signType = "RSA") {
		if($this->checkEmpty($this->rsaPrivateKeyFilePath)){
			$priKey=$this->rsaPrivateKey;
			$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
				wordwrap($priKey, 64, "\n", true) .
				"\n-----END RSA PRIVATE KEY-----";
		}else {
			$priKey = file_get_contents($this->rsaPrivateKeyFilePath);
			$res = openssl_get_privatekey($priKey);
		}

		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置'); 

		if ("RSA2" == $signType) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}

		if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
			openssl_free_key($res);
		}

		$sign = base64_encode($sign);
		return $sign;
	}

	protected function curl($url, $postFields = null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$postBodyString = "";
		$encodeArray = Array();
		$postMultipart = false;


		if (is_array($postFields) && 0 < count($postFields)) {

			foreach ($postFields as $k => $v) {
				if ("@" != substr($v, 0, 1)) //判断是不是文件上传
				{

					$postBodyString .= "$k=" . urlencode($this->characet($v, $this->postCharset)) . "&";
					$encodeArray[$k] = $this->characet($v, $this->postCharset);
				} else //文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
					$encodeArray[$k] = new \CURLFile(substr($v, 1));
				}

			}
			unset ($k, $v);
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
			}
		}

		if ($postMultipart) {

			$headers = array('content-type: multipart/form-data;charset=' . $this->postCharset . ';boundary=' . $this->getMillisecond());
		} else {

			$headers = array('content-type: application/x-www-form-urlencoded;charset=' . $this->postCharset);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$reponse = curl_exec($ch);

		if (curl_errno($ch)) {

			throw new Exception(curl_error($ch), 0);
		} else {
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) {
				throw new Exception($reponse, $httpStatusCode);
			}
		}

		curl_close($ch);
		return $reponse;
	}

	public function paramsExecute($request)
	{
		//组装系统参数
		$params["app_id"] = $this->appId;
		$params["method"] = $request->getApiMethodName();
		$params["version"] = $this->apiVersion;
		$params["format"] = $this->format;
		$params["sign_type"] = $this->signType;
		$params["timestamp"] = date("Y-m-d H:i:s");
		$params["notify_url"] = $this->notify_url;
		$params["return_url"] = $this->return_url;
		$params["charset"] = $this->postCharset;
		$params['biz_content'] = $request->getBizContent();
		
		$params["sign"] = $this->generateSign($params, $this->signType);

		return $params;
	}

	public function execute($request, $authToken = null, $appInfoAuthtoken = null) {

		$this->setupCharsets($request);

		//		//  如果两者编码不一致，会出现签名验签或者乱码
		if (strcasecmp($this->fileCharset, $this->postCharset)) {

			// writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
			throw new Exception("文件编码：[" . $this->fileCharset . "] 与表单提交编码：[" . $this->postCharset . "]两者不一致!");
		}

		//组装系统参数
		$sysParams["app_id"] = $this->appId;
		$sysParams["version"] = $iv;
		$sysParams["format"] = $this->format;
		$sysParams["sign_type"] = $this->signType;
		$sysParams["method"] = $request->getApiMethodName();
		$sysParams["timestamp"] = date("Y-m-d H:i:s");
		$sysParams["auth_token"] = $authToken;
		$sysParams["alipay_sdk"] = $this->alipaySdkVersion;
		$sysParams["terminal_type"] = $request->getTerminalType();
		$sysParams["terminal_info"] = $request->getTerminalInfo();
		$sysParams["product_code"] = $request->getProdCode();
		$sysParams["notify_url"] = $this->notify_url;
		$sysParams["charset"] = $this->postCharset;
		$sysParams["app_auth_token"] = $appInfoAuthtoken;

		//获取业务参数
		$apiParams = $request->getApiParas();

		if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			$sysParams["encrypt_type"] = $this->encryptType;

			if ($this->checkEmpty($apiParams['biz_content'])) {

				throw new Exception(" api request Fail! The reason : encrypt request is not supperted!");
			}

			if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {

				throw new Exception(" encryptType and encryptKey must not null! ");
			}

			if ("AES" != $this->encryptType) {

				throw new Exception("加密类型只支持AES");
			}

			// 执行加密
			$enCryptContent = AopEncrypt::encrypt($apiParams['biz_content'], $this->encryptKey);
			$apiParams['biz_content'] = $enCryptContent;

		}


		//签名
		$sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams), $this->signType);


		//系统参数放入GET请求串
		$requestUrl = $this->gatewayUrl . "?";
		foreach ($sysParams as $sysParamKey => $sysParamValue) {
			$requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->postCharset)) . "&";
		}
		$requestUrl = substr($requestUrl, 0, -1);

		//发起HTTP请求
		try {
			$resp = $this->curl($requestUrl, $apiParams);
		} catch (Exception $e) {

			$this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_ERROR_" . $e->getCode(), $e->getMessage());
			return false;
		}

		//解析AOP返回结果
		$respWellFormed = false;


		// 将返回结果转换本地文件编码
		$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);



		$signData = null;

		if ("json" == $this->format) {

			$respObject = json_decode($r);
			if (null !== $respObject) {
				$respWellFormed = true;
				$signData = $this->parserJSONSignData($request, $resp, $respObject);
			}
		} else if ("xml" == $this->format) {

			$respObject = @ simplexml_load_string($resp);
			if (false !== $respObject) {
				$respWellFormed = true;

				$signData = $this->parserXMLSignData($request, $resp);
			}
		}


		//返回的HTTP文本不是标准JSON或者XML，记下错误日志
		if (false === $respWellFormed) {
			$this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_RESPONSE_NOT_WELL_FORMED", $resp);
			return false;
		}

		// 验签
		$this->checkResponseSign($request, $signData, $resp, $respObject);

		// 解密
		if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			if ("json" == $this->format) {


				$resp = $this->encryptJSONSignSource($request, $resp);

				// 将返回结果转换本地文件编码
				$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
				$respObject = json_decode($r);
			}else{

				$resp = $this->encryptXMLSignSource($request, $resp);

				$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
				$respObject = @ simplexml_load_string($r);

			}
		}

		return $respObject;
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
}