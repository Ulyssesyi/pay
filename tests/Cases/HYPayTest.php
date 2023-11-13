<?php
declare(strict_types=1);

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class HYPayTest extends TestCase
{
    private $domain;
    private $orgId;
    private $merchantCode;
    private $merchantId;
    private $productId;
    private $orgPrivateRSAKey;
    private $tradeNo;

    protected function setUp(): void
    {
        $this->domain = getenv('HY_DOMAIN');
        $this->orgId = getenv('HY_ORIGIN_ID');
        $this->merchantCode = getenv('HY_MERCHANT_CODE');
        $this->merchantId = getenv('HY_MERCHANT_ID');
        $this->productId = getenv('HY_PRODUCT_ID');
        $this->orgPrivateRSAKey = getenv('HY_PRIVATE_KEY');
        $this->tradeNo = 'TS-' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.02;
        $config->subject = '起飞';
        $config->authCode = '131637589020606853';
        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $this->assertTrue(!!$config->authCode, '未填入付款码');
        var_dump($this->tradeNo);

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], 'B扫C预期成功未实现' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testBarcodePayIng()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 10000;
        $config->subject = '起飞';
        $config->authCode = '133604666392978169';
        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $this->assertTrue(!!$config->authCode, '未填入付款码');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], 'B扫C预期支付中未实现' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testBarcodePayFailure()
    {
        $tradeNo = 'TS-' . time();
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 100;
        $config->subject = '起飞';
        $config->authCode = '1231231';
        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testWebPayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = getenv('HY_PAY_SUCCESS_TRADE');

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $this->assertTrue(!!$config->tradeNo, '请填入订单号');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @depends testBarcodePayIng
     */
    public function testQueryPaying()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = $this->tradeNo;

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = 'NTS-' . time();

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = 'TP202311131628570005349041';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = 'TP202311131628570005349041';

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_HY;
        $config->tradeNo = 'NTS-' . time();

        $config->originIdHY = $this->orgId;
        $config->merchantCodeHY = $this->merchantCode;
        $config->merchantIdHY = $this->merchantId;
        $config->productIdHY = $this->productId;
        $config->privateKeyHY = $this->orgPrivateRSAKey;
        $config->domainHY = $this->domain;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}
