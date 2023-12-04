<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class AliPayTest extends TestCase
{
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS-' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->authCode = '284129140845289263';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $this->assertTrue(!!$config->authCode, '未填入付款码');
        var_dump($this->tradeNo);

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, 'B扫C预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testBarcodePayIng()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 100000;
        $config->subject = '起飞';
        $config->authCode = '280958516674862100';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $this->assertTrue(!!$config->authCode, '未填入付款码');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], 'B扫C预期支付中未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testBarcodePayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C预期支付失败未实现');
        $this->assertSame(Config::PAY_FAIL, $res['data']['trade_status'], 'B扫C预期支付失败未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->appAuthToken = '202303BBc07b1fb06ced431e895ed4bfea99eX61';
//        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
//        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
//        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertTrue($res['result'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        var_dump($this->tradeNo);
        var_dump($res['data']['payUrl']);
    }

    public function testQrcodePayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->appid = '1231231';
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userId = getenv('ALIPAY_BUYER_ID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertTrue($res['result'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('trade_no', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userId = getenv('WX_OPENID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = getenv('ALIPAY_SUCCESS_TRADE');

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $this->assertTrue(!!$config->tradeNo, '请填入订单号');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaying() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = 'TS-1631865966';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = 'NTS-' . time();

        $config->appid = '1231';
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = 'TS-1631862319';
        $config->totalAmount = 0.01;

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = 'TS-1631862319';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = 'NTS-' . time();

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayCertPath = BASE_PATH . '/cert/alipayCertPublicKey_RSA2.crt';
        $config->alipayRootCertPath = BASE_PATH . '/cert/alipayRootCert.crt';
        $config->merchantCertPath = BASE_PATH . '/cert/appCertPublicKey_2021001157630664.crt';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
