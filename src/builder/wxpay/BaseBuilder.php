<?php

namespace Apay\builder\wxpay;

use Apay\util\Common;
use Apay\util\Curl;
use Apay\util\DataParse;

class BaseBuilder
{
	protected $appid;
	protected $mch_id;
	protected $key;
	protected $nonce_str;

	public function __construct($config)
	{
		foreach ($config as $k => $v) {
			switch ($k) {
				case 'appid':
					$this->appid = $v;
					break;
				case 'mch_id':
					$this->mch_id = $v;
					break;
				case 'key':
					$this->key = $v;
					break;
				default:
					break;
			}
		}
	}

	public function MakeSign($params)
	{
		$string = DataParse::ToUrlParams($params);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	
	public function buildParams($order)
	{
		$urlParams = [
			'appid' => $this->appid,
			'mch_id' => $this->mch_id,
			'nonce_str' => Common::getNonceStr(),
			'time_start' => date("YmdHis"),
			'time_expire' => date("YmdHis", time() + 600),
			'spbill_create_ip' => Curl::getClientIp(),
		];

		$urlParams =  array_merge($urlParams,$order);
		$urlParams['sign'] = $this->MakeSign($urlParams);
		return $urlParams;
	}
}