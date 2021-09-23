<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;
use Yijin\Pay\Payment\LiantuoPay;

class LiantuoPayTest extends TestCase
{
    private $merchantNo;
    private $appId;
    private $appKey;
    private $tradeNo;

    protected function setUp(): void
    {
        $this->merchantNo = getenv('LT_MERCHANT_CODE');
        $this->appId = getenv('LT_APPID');
        $this->appKey = getenv('LT_APP_KEY');
        $this->tradeNo = 'TS-' . time();
    }

    public function testAuth() {
        $config = new Config();
        $config->userNameLT = getenv('LT_USERNAME');
        $config->userPwdLt = getenv('LT_PASSWORD');

        $res = (new LiantuoPay($config))->auth();

        $this->assertTrue($res['result'], '登录获取支付参数失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('appId', $res['data'], '登录获取支付参数失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        var_dump($res['data']);
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->authCode = '284659030375232252';

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

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
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 1100;
        $config->subject = '起飞';
        $config->authCode = '289551445277722493';

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

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
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

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
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->merchantCodeLt = '1231';
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->userId = getenv('LS_OPENID');
        $config->appid = getenv('LS_APPID');

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
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
        $config->channel = Config::PAY_BY_LT;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->userId = getenv('LS_OPENID');
        $config->appid = getenv('LS_APPID');

        $config->merchantCodeLt = '1232313321';
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = getenv('LT_PAY_SUCCESS_TRADE');

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $this->assertTrue(!!$config->tradeNo, '请填入订单号');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaying() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = 'TS-1632384575';

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = 'NTS-' . time();

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * 无退款权限，暂时未测试退款流程
     */
    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = 'TS-1632384575';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertContains($res['data']['refund_status'], [Config::REFUNDING, Config::REFUND_SUCCESS], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        var_dump($this->tradeNo);
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = 'TS-1632384575"';
        $config->refundTradeNo = 'TS1632365000';

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        var_dump($res);
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LT;
        $config->tradeNo = 'NTS-' . time();

        $config->merchantCodeLt = $this->merchantNo;
        $config->appIdLt = $this->appId;
        $config->appKeyLt = $this->appKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
