<?php
namespace Yijin\Pay;

use Yijin\Pay\Payment\Base;
use Yijin\Pay\Payment\SxfPay;

class Factory
{
    /**
     * @param Config $config
     * @return Base | SxfPay
     * @throws \Exception
     */
    function getAdapter(Config $config): Base {
        switch ($config->channel) {
            case Config::PAY_BY_SXF:
                return new SxfPay($config);
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }
}
