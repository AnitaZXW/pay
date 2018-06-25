<?php 

namespace Apay;

use Apay\builder\alipay\WapBuilder;
use Apay\builder\alipay\QueryBuilder;

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

	public function query($order)
	{
		$query = new QueryBuilder($this->commonConfig);
		return $query->run($order);
	}
}