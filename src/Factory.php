<?php
namespace Yijin\Pay;

use Yijin\Pay\Payment\Alipay;
use Yijin\Pay\Payment\Base;
use Yijin\Pay\Payment\FuBeiPay;
use Yijin\Pay\Payment\LeshuaPay;
use Yijin\Pay\Payment\LiantuoPay;
use Yijin\Pay\Payment\SQBPay;
use Yijin\Pay\Payment\SxfPay;
use Yijin\Pay\Payment\WeixinPay;

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
            case Config::PAY_BY_SQB:
                return new SQBPay($config);
            case Config::PAY_BY_LS:
                return new LeshuaPay($config);
            case Config::PAY_BY_LT:
                return new LiantuoPay($config);
            case Config::PAY_BY_FB:
                return new FuBeiPay($config);
            case Config::PAY_BY_OFFICIAL:
                return $config->payType === Config::WE_PAY ? new WeixinPay($config) : new Alipay($config);
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }
}
