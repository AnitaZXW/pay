<?php

namespace Apay\builder\alipay;

use Apay\builder\alipay\BaseBuilder;

class QueryBuilder extends BaseBuilder
{
    public function getApiMethodName()
    {
        return 'alipay.trade.query';
    }
}
