<?php 

namespace Apay\util\DataParse;


class DataParse
{
	
	/**
	 * 微信签名
	 */
	public function MakeSign($values)
	{
		$string = $this->ToUrlParams($values);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".WxPayConfig::KEY;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	/**
	 * 阿里云签名
	 */
	public function generateSign($values, $sign_type = "RSA2")
	{
		return $this->sign($this->ToUrlParams($values), $sign_type);
	}

	/**
	 * 输出xml字符
	 */
	public function ToXml($values)
	{
		if(!is_array($values) 
			|| count($values) <= 0)
		{
    		throw new WxPayException("数组数据异常！");
    	}
    	
    	$xml = "<xml>";
    	foreach ($values as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}
	
    /**
     * 将xml转为array
     */
	public function FromXml($xml)
	{	
		if(!$xml){
			throw new WxPayException("xml数据异常！");
		}
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $values;
	}
	
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams($values)
	{
		$buff = "";
		$values = ksort($values);
		foreach ($values as $k => $v)
		{
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
				if($k != "sign" && $v != "" && !is_array($v)){
					$buff .= $k . "=" . $v . "&";
				}
			}
			
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 * 检测是否为空
	 */
	public function checkEmpty($value) {
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (trim($value) === "")
			return true;

		return false;
	}
	
	/**
	 * RSA加密
	 */
	protected function sign($params, $sign_type = "RSA2", $public_key)
	{
		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($public_key, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";

		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置'); 

		if ("RSA2" == $sign_type) {
			openssl_sign($params, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($params, $sign, $res);
		}

		$sign = base64_encode($sign);
		return $sign;
	}

}