<?php

namespace Apay\builder\alipay;

use Apay\util\DataParse;
use Apay\builder\BaseBuilder;

class WapPay extends BaseBuilder
{
    public function __construct($commonConfig)
    {
        parent::construct($commonConfig);
    }

    public function getApiMethodName()
    {
        return 'alipay.trade.wap.pay';
    }
}
