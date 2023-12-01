<?php
namespace Yijin\Pay;

use Yijin\Pay\AbroadPayment\Base as AbroadBase;
use Yijin\Pay\AbroadPayment\GKash;
use Yijin\Pay\AbroadPayment\HiPay;
use Yijin\Pay\AbroadPayment\IPay88;
use Yijin\Pay\AbroadPayment\StripePay;
use Yijin\Pay\Payment\Alipay;
use Yijin\Pay\Payment\Base;
use Yijin\Pay\Payment\FuBeiPay;
use Yijin\Pay\Payment\HYPay;
use Yijin\Pay\Payment\LeshuaPay;
use Yijin\Pay\Payment\LiantuoPay;
use Yijin\Pay\Payment\LtcPay;
use Yijin\Pay\Payment\SQBPay;
use Yijin\Pay\Payment\SxfPay;
use Yijin\Pay\Payment\UnionPay;
use Yijin\Pay\Payment\WeixinPay;

class Factory
{
    /**
     * @param Config $config
     * @return Base
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
            case Config::PAY_BY_YSF:
                return new UnionPay($config);
            case Config::PAY_BY_LTC:
                return new LtcPay($config);
            case Config::PAY_BY_HY:
                return new HYPay($config);
            case Config::PAY_BY_OFFICIAL:
                return $config->payType === Config::WE_PAY ? new WeixinPay($config) : new Alipay($config);
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }
    /**
     * @param AbroadConfig $config
     * @return AbroadBase
     * @throws \Exception
     */
    function getAbroadAdapter(AbroadConfig $config): AbroadBase {
        switch ($config->channel) {
            case AbroadConfig::PAY_BY_HIPAY:
                return new HiPay($config);
            case AbroadConfig::PAY_BY_IPAY88:
                return new IPay88($config);
            case AbroadConfig::PAY_BY_GKASH:
                return new GKash($config);
            case AbroadConfig::PAY_BY_STRIPE:
                return new StripePay($config);
            default:
                throw new \Exception('暂时未支持的支付通道');
        }
    }
}
