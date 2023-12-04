<?php
declare(strict_types=1);

use Yijin\Pay\AbroadConfig;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class GkashTest extends TestCase
{
    private string $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS' . time();
    }

    public function testWebSuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_GKASH;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->gKashCID = getenv('GKASH_CID');
        $config->gKashSignKey = getenv('GKASH_SIGN_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->webPay();
        $this->assertTrue($res['result'], '网页支付请求失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('jsApiParameters', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
