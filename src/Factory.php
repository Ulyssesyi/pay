<?php
namespace Yijin\Pay;

use Yijin\Pay\Config\Base as Config;
use Yijin\Pay\Config\SxfConfig;
use Yijin\Pay\Payment\Base;
use Yijin\Pay\Payment\SxfPay;

class Factory
{
    const PAY_BY_SXF = 10;

    /**
     * @param $type
     * @param Config $config
     * @return Base | SxfPay
     * @throws \Exception
     */
    function getAdapter($type, Config $config): Base {
        switch ((int)$type) {
            case self::PAY_BY_SXF:
                return new SxfPay($config);
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }

    /**
     * @param $type
     * @return Config | SxfConfig
     * @throws \Exception
     */
    function getConfig($type): Config {
        switch ((int)$type) {
            case self::PAY_BY_SXF:
                return new SxfConfig();
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }
}
