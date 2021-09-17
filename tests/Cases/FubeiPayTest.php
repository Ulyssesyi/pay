<?php
declare(strict_types=1);
namespace Cases;

use Yijin\Pay\Config;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class FubeiPayTest extends TestCase
{
    private $storeId;
    private $merchantId;
    private $merchantKey;
    private $tradeNo;
    protected function setUp(): void
    {
        $this->storeId = getenv('FB_STORE_ID');
        $this->merchantId = getenv('FB_MERCHANT_ID');
        $this->merchantKey = getenv('FB_MERCHANT_KEY');
        $this->tradeNo = 'TS-' . time();
    }

    public function testBarcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->authCode = '284484020100215751';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

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
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 100000;
        $config->subject = '起飞';
        $config->authCode = '283861959587153444';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

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
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $tradeNo;
        $config->totalAmount = 10000000000;
        $config->subject = '起飞';
        $config->authCode = '1231231';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->barcodePay();
        $this->assertFalse($res['result']);
    }

    public function testQrcodePaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

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
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::WE_PAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';

        $config->storeIdFb = '123';
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertFalse($res['result'], 'C扫B预期失败不成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    /**
     * todo 需要先通过auth获取用户信息，懒得测试了，等项目实际上线再测试吧
     */
    public function testWebPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->payType = Config::ALIPAY;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';
        $config->returnUrlSqb = 'https://www.baidu.com';
        $config->userId = 'obQAg5JBoTEbS8BmUzyPfV1jeCaE';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->webPay();
        $this->assertTrue($res['result'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        if ($config->payType === Config::ALIPAY) {
            $this->assertArrayHasKey('trade_no', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
            var_dump($res['data']['trade_no']);
        } else {
            $this->assertArrayHasKey('jsApiParameters', $res['data'], '网页支付失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
            var_dump($res['data']['jsApiParameters']);
        }
    }

    public function testQueryPaySuccess()
    {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = getenv('FB_PAY_SUCCESS_TRADE');

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $this->assertTrue(!!$config->tradeNo, '请填入订单号');

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAY_SUCCESS, $res['data']['trade_status'], '订单查询失败' . json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaying() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = 'TS-1631856259';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '订单查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::PAYING, $res['data']['trade_status'], '订单查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = 'NTS-' . time();

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->query();
        $this->assertFalse($res['result'], '订单查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = 'TS-1631856608';
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '订单退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUNDING, $res['data']['refund_status'], '订单退款失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
        var_dump($this->tradeNo);
    }

    public function testRefundFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refund();
        $this->assertFalse($res['result'], '订单退款预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQuerySuccess() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = 'TS-1631856502';
        $config->refundTradeNo = 'TS-1631856695';

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertTrue($res['result'], '订单退款查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(Config::REFUND_SUCCESS, $res['data']['refund_status'], '订单退款查询失败'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundQueryFailure() {
        $config = new Config();
        $config->channel = Config::PAY_BY_FB;
        $config->tradeNo = 'NTS-' . time();

        $config->storeIdFb = $this->storeId;
        $config->merchantIdFb = $this->merchantId;
        $config->merchantKeyFb = $this->merchantKey;

        $payModel = (new Factory())->getAdapter($config);
        $res = $payModel->refundQuery();
        $this->assertFalse($res['result'], '订单退款查询预期失败未成功' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
