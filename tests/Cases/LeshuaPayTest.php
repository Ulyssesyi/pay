<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class LeshuaPayTest extends TestCase
{
    private $merchantNo;
    private $orgKey;
    private string $tradeNo;

    protected function setUp(): void
    {
        $this->merchantNo = getenv('LS_MERCHANT_ID');
        $this->orgKey = getenv('LS_TRANSACTION_KEY');
        $this->tradeNo = 'TS' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->authCode = '285763591489343608';

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

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
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 1100;
        $config->subject = '起飞';
        $config->authCode = '289551445277722493';

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

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
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;
        $config->jumpUrlLS = 'https://www.baidu.com';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertTrue($res['result'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        var_dump($res);
        var_dump($res['data']['payUrl']);
    }

    public function testQrcodePayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->merchantIdLS = '12312313';
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * @todo 暂时没有可以测试微信支付的账号
     */
    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->userId = getenv('LS_OPENID');
        $config->appid = getenv('LS_APPID');

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

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
        $config->channel = Config::PAY_BY_LS;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->userId = getenv('LS_OPENID');
        $config->appid = getenv('LS_APPID');

        $config->merchantIdLS = '123';
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = getenv('LS_PAY_SUCCESS_TRADE');

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

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
        sleep(3);
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = $this->tradeNo;

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = 'NTS-' . time();

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = 'TS1632365425';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertContains($res['data']['refund_status'], [Config::REFUNDING, Config::REFUND_SUCCESS], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = 'TS1632364452';
        $config->refundTradeNo = 'TS1632365000';

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        var_dump($res);
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_LS;
        $config->tradeNo = 'NTS-' . time();

        $config->merchantIdLS = $this->merchantNo;
        $config->serviceProviderKeyLS = $this->orgKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
