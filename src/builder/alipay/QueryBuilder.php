<?php

namespace Apay\builder\alipay;

use Apay\builder\BaseBuilder;

class Query extends BaseBuilder
{
    public function getApiMethodName()
    {
        return 'alipay.trade.query';
    }
}
