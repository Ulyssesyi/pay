<?php

namespace Cases;

use PHPUnit\Framework\TestCase;
use Yijin\Pay\AbroadConfig;
use Yijin\Pay\AbroadPayment\GKash;
use Yijin\Pay\AbroadPayment\HiPay;
use Yijin\Pay\AbroadPayment\IPay88;
use Yijin\Pay\AbroadPayment\NetsPay;
use Yijin\Pay\AbroadPayment\StripePay;
use Yijin\Pay\Config;
use Yijin\Pay\Factory;
use Yijin\Pay\Payment\Alipay;
use Yijin\Pay\Payment\FuBeiPay;
use Yijin\Pay\Payment\HYPay;
use Yijin\Pay\Payment\LeshuaPay;
use Yijin\Pay\Payment\LiantuoPay;
use Yijin\Pay\Payment\LtcPay;
use Yijin\Pay\Payment\SQBPay;
use Yijin\Pay\Payment\SxfPay;
use Yijin\Pay\Payment\UnionPay;
use Yijin\Pay\Payment\WeixinPay;

class FactoryTest extends TestCase
{
    public function testSxfAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(SxfPay::class, $payModel, '工厂实例化随行付渠道失败');
    }

    public function testWeixinAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::WE_PAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(WeixinPay::class, $payModel, '工厂实例化微信直连支付失败');
    }

    public function testAlipayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(Alipay::class, $payModel, '工厂实例化支付宝直连支付失败');
    }

    public function testSQBPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(SQBPay::class, $payModel, '工厂实例化收钱吧支付失败');
    }

    public function testFubeiPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(FuBeiPay::class, $payModel, '工厂实例化付呗支付失败');
    }

    public function testLeshuaPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(LeshuaPay::class, $payModel, '工厂实例化付呗支付失败');
    }

    public function testLiantuoPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(LiantuoPay::class, $payModel, '工厂实例化联付通支付失败');
    }

    public function testUnionPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_YSF;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(UnionPay::class, $payModel, '工厂实例化云闪付支付失败');
    }

    public function testLtcPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LTC;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(LtcPay::class, $payModel, '工厂实例化乐天成支付失败');
    }

    public function testHYPayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(HYPay::class, $payModel, '工厂实例化杭研支付失败');
    }


    public function testGKashAdapter() {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_GKASH;
        $payModel = (new Factory())->getAbroadAdapter($config);
        $this->assertInstanceOf(GKash::class, $payModel, '工厂实例化GKash渠道失败');
    }

    public function testHiPayAdapter() {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $payModel = (new Factory())->getAbroadAdapter($config);
        $this->assertInstanceOf(HiPay::class, $payModel, '工厂实例化HiPay渠道失败');
    }

    public function testIPay88Adapter() {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $payModel = (new Factory())->getAbroadAdapter($config);
        $this->assertInstanceOf(IPay88::class, $payModel, '工厂实例化IPay88渠道失败');
    }

    public function testStripePayAdapter() {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_STRIPE;
        $payModel = (new Factory())->getAbroadAdapter($config);
        $this->assertInstanceOf(StripePay::class, $payModel, '工厂实例化Stripe渠道失败');
    }

    public function testNetsPayAdapter() {
        $this->expectException(\Exception::class);
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $payModel = (new Factory())->getAbroadAdapter($config);
        $this->assertInstanceOf(NetsPay::class, $payModel, '工厂实例化Nets渠道失败');
    }

    public function testNoChannelAdapter() {
        $this->expectException(\Exception::class);
        $config = new Config();
        $config->channel = 0;
        $config->payType = Config::ALIPAY;
        (new Factory())->getAdapter($config);
    }
}
