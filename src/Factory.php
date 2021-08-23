<?php
namespace Yijin\Pay;

use Yijin\Pay\Config\Base as Config;
use Yijin\Pay\Payment\Base;
use Yijin\Pay\Payment\SxfPay;

class Factory
{
    const PAY_BY_SXF = 10;

    function getAdapter($type, Config $config): Base {
        switch ((int)$type) {
            case self::PAY_BY_SXF:
                return new SxfPay($config);
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }
}
