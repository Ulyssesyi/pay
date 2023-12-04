<?php
declare(strict_types=1);

use Yijin\Pay\AbroadConfig;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class HiPayTest extends TestCase
{
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->authCode = '';
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, 'B扫C预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    public function testQrcodePaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertTrue($res['result'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
    public function testWebPaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->webPay();
        $this->assertTrue($res['result'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
    public function testQuerySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, '查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    public function testQueryFailedSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_FAIL, $res['data']['trade_status'] ?? 0, '查询预期失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    public function testRefundSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = '';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_SUCCESS, $res['data']['refund_status'] ?? 0, '退款预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    public function testRefundFailedSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = '';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_FAIL, $res['data']['refund_status'] ?? 0, '退款预期失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    public function testRefundQuerySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = '';
        $config->refundTradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_SUCCESS, $res['data']['refund_status'] ?? 0, '退款查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    public function testRefundQueryFailedSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_HIPAY;
        $config->tradeNo = '';
        $config->refundTradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->hipayAppId = getenv('HIPAY_APP_ID');
        $config->hiPayPrivateKey = getenv('HIPAY_PRIVATE_KEY');
        $config->hiPayPublicKey = getenv('HIPAY_PUBLIC_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_FAIL, $res['data']['refund_status'] ?? 0, '退款查询预期失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}
