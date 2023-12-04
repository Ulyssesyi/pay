<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;
use Yijin\Pay\Payment\SQBPay;

class SQBPayTest extends TestCase
{
    private $terminalSN;
    private $terminalKey;
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->terminalSN = getenv('SQB_TERM_SN');
        $this->terminalKey = getenv('SQB_TERM_KEY');
        $this->tradeNo = 'TS-' . time();
    }

    public function testActivate() {
        $config = new Config();
        $config->serviceProviderIDSqb = getenv('SQB_SP_ID');
        $config->terminalSNSqb = getenv('SQB_SP_SN');
        $config->terminalKeySqb = getenv('SQB_SP_KEY');
        $config->activateCodeSqb = getenv('SQB_CODE');
        $config->activateDeviceIDSqb = getenv('SQB_DEVICE_ID');

        $res = (new SQBPay($config))->activate();
        $this->assertTrue($res['result'], '设备激活失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertNotEmpty($res['data']['terminal_sn'], '设备激活失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertNotEmpty($res['data']['terminal_key'], '设备激活失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        var_dump($res);
    }

    public function testCheckIn() {
        $config = new Config();
        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;
        $config->activateDeviceIDSqb = getenv('SQB_DEVICE_ID');

        $res = (new SQBPay($config))->checkIn();
        $this->assertTrue($res['result'], '设备刷新失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertNotEmpty($res['data']['terminal_key'], '设备刷新失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        var_dump($res);
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->authCode = '';

        $config->operatorSqb = 'Test';
        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $this->assertTrue(!!$config->authCode, '未填入付款码');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], 'B扫C预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
        var_dump($this->tradeNo);
    }

    public function testBarcodePayIng()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 100000;
        $config->subject = '起飞';
        $config->authCode = '';

        $config->operatorSqb = 'Test';
        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

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
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';


        $config->operatorSqb = 'Test';
        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->operatorSqb = 'Test';
        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

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
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->operatorSqb = 'Test';
        $config->terminalSNSqb = '12312';
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';
        $config->returnUrlSqb = 'https://www.baidu.com';

        $config->operatorSqb = 'Test';
        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertTrue($res['result'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        var_dump($res['data']['payUrl']);
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = getenv('SQB_PAY_SUCCESS_TRADE');

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

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
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = $this->tradeNo;

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = 'NTS-' . time();

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = 'TS-1631762669';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        return $config->tradeNo;
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = 'TS-1631762669';

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_SQB;
        $config->tradeNo = 'NTS-' . time();

        $config->terminalSNSqb = $this->terminalSN;
        $config->terminalKeySqb = $this->terminalKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_FAIL, $res['data']['refund_status'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
