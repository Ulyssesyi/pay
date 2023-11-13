<?php

namespace Cases;

use PHPUnit\Framework\TestCase;
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

    public function testNoChannelAdapter() {
        $this->expectException(\Exception::class);
        $config = new Config();
        $config->channel = 0;
        $config->payType = Config::ALIPAY;
        (new Factory())->getAdapter($config);
    }
}
