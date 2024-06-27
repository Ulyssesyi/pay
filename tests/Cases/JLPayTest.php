<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;
use Yijin\Pay\Merchant\JLMerchant;

class JLPayTest extends TestCase
{
    private $orgId;
    private $merchantNo;
    private $termNo;
    private $orgPrivateRSAKey;
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->orgId = getenv('JL_ORG_ID');
        $this->merchantNo = getenv('JL_MERCHANT_ID');
        $this->termNo = getenv('JL_TERM_NO');
        $this->orgPrivateRSAKey = getenv('JL_ORG_RSA_KEY');
        $this->tradeNo = 'TS-' . time();
    }

    public function testClientAddDevice()
    {
        $model = new JLMerchant();
        $model->isSandbox = true;
        $model->agentId = $this->orgId;
        $model->merchantNo = $this->merchantNo;
        $model->privateKey = $this->orgPrivateRSAKey;
        $res = $model->clientAddQrDevice();
        $this->assertTrue($res['result'], '设备添加失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        var_dump($res['data']);
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->authCode = '131582883324937165';
        $config->userIP = '127.0.0.1';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

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
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 10000;
        $config->subject = '起飞';
        $config->authCode = '130220981494329207';
        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->userIP = '127.0.0.1';
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

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
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';
        $config->userIP = '127.0.0.1';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userIP = '127.0.0.1';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

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
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userIP = '127.0.0.1';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userIP = '127.0.0.1';
        $config->userId = getenv('JL_OPENID');
        $config->subAppId = getenv('JL_APPID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

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
        $config->channel = Config::PAY_BY_JL;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->userIP = '127.0.0.1';
        $config->userId = getenv('JL_OPENID');
        $config->appid = getenv('JL_APPID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->tradeNo = getenv('JL_PAY_SUCCESS_TRADE');

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

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
        $config->channel = Config::PAY_BY_JL;
        $config->tradeNo = $this->tradeNo;

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->tradeNo = 'NTS-' . time();

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->tradeNo = 'TS-1719482076';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->userIP = '127.0.0.1';

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * @depends testRefundSuccess
     */
    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->refundTradeNo = $this->tradeNo;

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_JL;
        $config->tradeNo = 'NTS-' . time();

        $config->orgCodeJL = $this->orgId;
        $config->merchantIdJL = $this->merchantNo;
        $config->privateKeyJL = $this->orgPrivateRSAKey;
        $config->termNoJL = $this->termNo;
        $config->isSandboxJL = true;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
