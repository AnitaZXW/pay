<?php 

namespace Apay\util;


class DataParse
{
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
	
	public function ToUrlParams($params)
	{
		$buff = "";
		ksort($params);
		foreach ($params as $k => $v)
		{
			if(!empty($v) && "@" != substr($v, 0, 1)){
				$v = self::characet($v, 'utf-8');
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

	public function ToUrlencodeParams($params)
	{
		$buff = "";
		ksort($params);
		foreach ($params as $k => $v)
		{
			if(!empty($v) && "@" != substr($v, 0, 1)){
				$v = self::characet($v, 'utf-8');
				$buff .= $k . "=" . urlencode($v) . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}

	private function characet($data, $targetCharset) {
		
		if (!empty($data)) {
			$data = mb_convert_encoding($data, $targetCharset, $targetCharset);
		}

		return $data;
	}

}