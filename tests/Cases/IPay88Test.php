<?php
declare(strict_types=1);

use Yijin\Pay\AbroadConfig;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class IPay88Test extends TestCase
{
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->authCode = '';
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, 'B扫C预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQuerySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = $this->tradeNo;

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, '查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailed() {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = $this->tradeNo;

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_FAIL, $res['data']['trade_status'] ?? 0, '查询预期失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = '';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->notifyUrl = 'https://www.baidu.com';

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_SUCCESS, $res['data']['refund_status'] ?? 0, '退款预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundFailed()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = 'RF' . time();
        $config->totalAmount = 0.01;
        $config->notifyUrl = 'https://www.baidu.com';

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_FAIL, $res['data']['refund_status'] ?? 0, '退款预期失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = '';
        $config->refundTradeNo = '';

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_SUCCESS, $res['data']['refund_status'] ?? 0, '退款查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailed()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = '';
        $config->refundTradeNo = 'RF' . time();

        $config->iPay88MerchantCode = getenv('IPAY88_MERCHANT_CODE');
        $config->iPay88MerchantKey = getenv('IPAY88_MERCHANT_KEY');
        $config->iPay88MerchantName = getenv('IPAY88_MERCHANT_NAME');
        $config->iPay88MerchantContact = getenv('IPAY88_MERCHANT_CONTACT');
        $config->iPay88MerchantEmail = getenv('IPAY88_MERCHANT_EMAIL');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_FAIL, $res['data']['refund_status'] ?? 0, '退款查询预期失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}
