<?php

namespace Apay\builder\alipay;

use Apay\util\DataParse;
use Apay\builder\alipay\BaseBuilder;

class WapBuilder extends BaseBuilder
{
    public function __construct($commonConfig)
    {
        parent::__construct($commonConfig);
    }

    public function getApiMethodName()
    {
        return 'alipay.trade.wap.pay';
    }
}
