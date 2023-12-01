<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class SxfPayTest extends TestCase
{
    private $orgId;
    private $merchantNo;
    private $orgPrivateRSAKey;
    private $tradeNo;
    protected function setUp(): void
    {
        $this->orgId = getenv('SXF_ORG_ID');
        $this->merchantNo = getenv('SXF_MERCHANT_NO');
        $this->orgPrivateRSAKey = getenv('SXF_ORG_RSA_KEY');
        $this->tradeNo = 'TS-' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.1;
        $config->subject = '起飞';
        $config->authCode = '134729175574290721';
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $this->assertTrue(!!$config->authCode, '未填入付款码');
        var_dump($this->tradeNo);

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], 'B扫C预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testBarcodePayIng()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 10000;
        $config->subject = '起飞';
        $config->authCode = '134551963473559946';
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $this->assertTrue(!!$config->authCode, '未填入付款码');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], 'B扫C预期支付中未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testBarcodePayFailure()
    {
        $tradeNo = 'TS-' . time();
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

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
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = '1231231';
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userIP = '127.0.0.1';
        $config->userId = getenv('SXF_OPENID');
        $config->subAppId = getenv('SXF_APPID');
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isMiniProgram = 1;

        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        var_dump(json_encode($res));
        $this->assertTrue($res['result'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        if ($config->payType === Config::WE_PAY) {
            $this->assertArrayHasKey('jsApiParameters', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        } else {
            $this->assertArrayHasKey('trade_no', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        }
    }

    public function testWebPayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->orgIdSxf = $this->orgId;
        $config->userIP = '127.0.0.1';
        $config->userId = getenv('SXF_OPENID');
        $config->appid = getenv('SXF_APPID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = '123';
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = getenv('SXF_PAY_SUCCESS_TRADE');
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $this->assertTrue(!!$config->tradeNo, '请填入订单号');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @depends testQrcodePaySuccess
     */
    public function testQueryPaying() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = $this->tradeNo;
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = 'NTS-' . time();
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = 'TS-1631606186';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * @depends testRefundSuccess
     */
    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->refundTradeNo = $this->tradeNo;
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = 'NTS-' . time();
        $config->orgIdSxf = $this->orgId;
        $config->merchantNoSxf = $this->merchantNo;
        $config->orgPrivateRSAKeySxf = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
