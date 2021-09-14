<?php

namespace Cases;

use PHPUnit\Framework\TestCase;
use Yijin\Pay\Config;
use Yijin\Pay\Factory;
use Yijin\Pay\Payment\Alipay;
use Yijin\Pay\Payment\SxfPay;
use Yijin\Pay\Payment\Weixin;

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
        $this->assertInstanceOf(Weixin::class, $payModel, '工厂实例化微信直连支付失败');
    }

    public function testAlipayAdapter() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $payModel = (new Factory())->getAdapter($config);
        $this->assertInstanceOf(Alipay::class, $payModel, '工厂实例化支付宝直连支付失败');
    }

    public function testNoChannelAdapter() {
        $this->expectException(\Exception::class);
        $config = new Config();
        $config->channel = 0;
        $config->payType = Config::ALIPAY;
        (new Factory())->getAdapter($config);
    }
}
