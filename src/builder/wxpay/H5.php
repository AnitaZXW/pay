<?php

namespace Apay\builder\wxpay;

use Apay\builder\wxpay\BaseBuilder;

class H5 extends BaseBuilder
{	
	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function getApiGateWayUrl()
	{
		return 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	}

	public function getTradeType()
	{
		return 'MWEB';
	}
}