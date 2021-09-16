<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;
use Yijin\Pay\Payment\SxfPay;

class AliPayTest extends TestCase
{
    private $tradeNo;
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
        $config->authCode = '134664532889881598';

        $config->appid = getenv('ALIPAY_APPID');
        $config->merchantPrivateKey = getenv('ALIPAY_MERCHANT_PRIVATE_KEY');
        $config->alipayPublicKey = getenv('ALIPAY_PUBLIC_KEY');

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
        $config->totalAmount = 10000;
        $config->subject = '起飞';
        $config->authCode = '134551963473559946';
        $config->userIP = '127.0.0.1';

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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
        $config->userIP = '127.0.0.1';

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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
        $config->userIP = '127.0.0.1';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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
        $config->userIP = '127.0.0.1';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = '12312312';
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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
        $config->userIP = '127.0.0.1';
        $config->userId = getenv('WX_OPENID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertTrue($res['result'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('jsApiParameters', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testWebPayFailure()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->orgId = $this->orgId;
        $config->userIP = '127.0.0.1';
        $config->userId = getenv('WX_OPENID');
        $config->notifyUrl = 'https://www.baidu.com';

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertFalse($res['result'], '网页支付预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = getenv('WX_PAY_SUCCESS_TRADE');

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_FAIL, $res['data']['trade_status'], '订单查询预期失败未成功'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = 'TS-1631608809';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.2;

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');
        $config->clientApiV2KeyFilePath = BASE_PATH . '/cert/apiclient_key.pem';
        $config->clientApiV2CertFilePath = BASE_PATH . '/cert/apiclient_cert.pem';

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        return $this->tradeNo;
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * @depends testRefundSuccess
     */
    public function testRefundQuerySuccess($refundTradeNo) {
        $config = new Config();
        $config->channel = Config::PAY_BY_OFFICIAL;
        $config->payType = Config::ALIPAY;
        $config->refundTradeNo = $refundTradeNo;

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

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

        $config->appid = getenv('WX_APPID');
        $config->mchId = getenv('WX_MCH_ID');
        $config->subMchId = getenv('WX_SUB_MCH_ID');
        $config->apiV2Key = getenv('WX_V2_API_KEY');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_FAIL, $res['data']['refund_status'], '订单退款查询预期失败未成功'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}
