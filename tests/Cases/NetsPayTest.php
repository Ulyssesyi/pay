<?php
declare(strict_types=1);

use Yijin\Pay\AbroadConfig;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class NetsPayTest extends TestCase
{
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS' . time();
    }

    public function testQrcodePaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 1;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://webhook.site/89aa8f5f-eb16-4512-a534-24eeb0dd2fec';
        $config->isSandbox = true;

        $config->netsKey = getenv('NET_KEY');
        $config->netsKeyId = getenv('NET_KEY_ID');
        $config->netsMID = getenv('NET_MID');
        $config->netsTID = getenv('NET_TID');
        $config->netsSTAN = '100002';

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->qrcodePay();
        var_dump($res);
        $this->assertTrue($res['result'], '二维码支付请求失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }

    public function testQuerySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->netsKey = getenv('NET_KEY');
        $config->netsKeyId = getenv('NET_KEY_ID');
        $config->netsMID = getenv('NET_MID');
        $config->netsTID = getenv('NET_TID');
        $config->netsSTAN = '100001';
        $config->netsTxnIdentifier = 'NETSQPAY037066801####111370668001000013692558c010000010000010726e0492TEST EZI TECHNOLO295c';

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAY_SUCCESS, $res['data']['trade_status'] ?? 0, '查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testQueryPaying()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->isSandbox = true;

        $config->netsKey = getenv('NET_KEY');
        $config->netsKeyId = getenv('NET_KEY_ID');
        $config->netsMID = getenv('NET_MID');
        $config->netsTID = getenv('NET_TID');
        $config->netsSTAN = '100001';
        $config->netsTxnIdentifier = 'NETSQPAY037066801####1113706680010000136925e030100000100000102ab3206eTEST EZI TECHNOLO6aa3';

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->query();
        $this->assertTrue($res['result'], '查询失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::PAYING, $res['data']['trade_status'] ?? 0, '查询预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public function testRefundSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_NETS;
        $config->tradeNo = $this->tradeNo;
        $config->refundTradeNo = $this->tradeNo;
        $config->totalAmount = 1;
        $config->isSandbox = true;

        $config->netsKey = getenv('NET_KEY');
        $config->netsKeyId = getenv('NET_KEY_ID');
        $config->netsMID = getenv('NET_MID');
        $config->netsTID = getenv('NET_TID');
        $config->netsSTAN = '100002';
        $config->netsTxnIdentifier = 'NETSQPAY037066801####1113706680010000236925f0901000001000001040f4146eTEST EZI TECHNOLOef31';

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->refund();
        $this->assertTrue($res['result'], '退款失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertSame(AbroadConfig::REFUND_SUCCESS, $res['data']['refund_status'] ?? 0, '退款预期成功未实现'. json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}
