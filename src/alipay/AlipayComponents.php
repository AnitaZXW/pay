<?php 

namespace Apay\alipay;

use Apay\alipay\request\WapPay;

class AlipayComponents
{
	public $config;

	public function WapPay()
	{
		return new WapPay();
	}
}