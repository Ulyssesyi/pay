<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yijin\Pay\AbroadConfig;
use Yijin\Pay\Factory;

class MangoPayTest extends TestCase
{
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS' . time();
    }

    public function testQrcodePaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_MANGO;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 500.88;
        $config->notifyUrl = 'http://127.0.0.1:8080/cashier/notify';
        $config->isSandbox = true;

        $config->mangoMerchantNo = getenv('MANGO_MERCHANT_NO');
        $config->mangoMerchantKey = getenv('MANGO_MERCHANT_KEY');
        $config->mangoMerchantSalt = getenv('MANGO_MERCHANT_SALT');
        $config->mangoPlatformKey = getenv('MANGO_PLATFORM_KEY');
        $config->mangoPlatformSalt = getenv('MANGO_PLATFORM_SALT');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->qrcodePay();
        var_dump($res);
        $this->assertTrue($res['result'], '二维码支付请求失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQuerySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->mangoMerchantNo = getenv('MANGO_MERCHANT_NO');
        $config->mangoMerchantKey = getenv('MANGO_MERCHANT_KEY');
        $config->mangoMerchantSalt = getenv('MANGO_MERCHANT_SALT');
        $config->mangoPlatformKey = getenv('MANGO_PLATFORM_KEY');
        $config->mangoPlatformSalt = getenv('MANGO_PLATFORM_SALT');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, '查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaying()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->mangoMerchantNo = getenv('MANGO_MERCHANT_NO');
        $config->mangoMerchantKey = getenv('MANGO_MERCHANT_KEY');
        $config->mangoMerchantSalt = getenv('MANGO_MERCHANT_SALT');
        $config->mangoPlatformKey = getenv('MANGO_PLATFORM_KEY');
        $config->mangoPlatformSalt = getenv('MANGO_PLATFORM_SALT');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAYING, $res['data']['trade_status'] ?? 0, '查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 1;
        $config->isSandbox = true;

        $config->mangoMerchantNo = getenv('MANGO_MERCHANT_NO');
        $config->mangoMerchantKey = getenv('MANGO_MERCHANT_KEY');
        $config->mangoMerchantSalt = getenv('MANGO_MERCHANT_SALT');
        $config->mangoPlatformKey = getenv('MANGO_PLATFORM_KEY');
        $config->mangoPlatformSalt = getenv('MANGO_PLATFORM_SALT');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_SUCCESS, $res['data']['refund_status'] ?? 0, '退款预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}
