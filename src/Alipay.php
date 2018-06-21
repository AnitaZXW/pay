<?php 

namespace Apay;

class Alipay
{
	
	private $commonConfig;

	public function __construct($commonConfig)
	{
		$this->commonConfig = $commonConfig;
	}
	
	public function wap($order)
	{
		$wap = new WapBuilder($this->commonConfig);
		return $wap->buildParams($order);
	}

	public function query()
	{

	}

	public function refund()
	{

	}

}