<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;
use Yijin\Pay\Payment\SxfPay;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = '1231231';
        $config->userIP = '127.0.0.1';
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = getenv('SXF_PAY_SUCCESS_TRADE');
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = 'NTS-' . time();
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

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
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        return $this->tradeNo;
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * @depends testRefundSuccess
     */
    public function testRefundQuerySuccess($refundTradeNo) {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->refundTradeNo = $refundTradeNo;
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SXF;
        $config->tradeNo = 'NTS-' . time();
        $config->orgId = $this->orgId;
        $config->merchantNo = $this->merchantNo;
        $config->orgPrivateRSAKey = $this->orgPrivateRSAKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
