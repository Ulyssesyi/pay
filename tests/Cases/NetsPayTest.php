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
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';
        $config->isSandbox = true;

        $config->netsKey = getenv('NET_KEY');
        $config->netsKeyId = getenv('NET_KEY_ID');
        $config->netsMID = getenv('NET_MID');
        $config->netsTID = getenv('NET_TID');
        $config->netsSTAN = '000001';

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertTrue($res['result'], '二维码支付请求失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
