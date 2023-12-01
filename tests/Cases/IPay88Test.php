<?php
declare(strict_types=1);

use Yijin\Pay\AbroadConfig;
use PHPUnit\Framework\TestCase;
use Yijin\Pay\Factory;

class IPay88Test extends TestCase
{
    private $tradeNo;
    protected function setUp(): void
    {
        $this->tradeNo = 'TS' . time();
    }

    public function testQrcodePaySuccess()
    {
        $config = new AbroadConfig();
        $config->channel = AbroadConfig::PAY_BY_IPAY88;
        $config->tradeNo = $this->tradeNo;
        $config->totalAmount = 0.01;
        $config->subject = '起飞';
        $config->notifyUrl = 'https://www.baidu.com';

        $config->kbzAppId = getenv('KBZ_APPID');
        $config->kbzMerchantCode = getenv('KBZ_MCH_ID');
        $config->kbzMerchantKey = getenv('KBZ_MCH_KEY');

        $payModel = (new Factory())->getAbroadAdapter($config);
        $res = $payModel->qrcodePay();
        $this->assertTrue($res['result'], 'B扫C失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
        $this->assertArrayHasKey('payUrl', $res['data'], 'C扫B失败' . json_encode($res,  JSON_UNESCAPED_UNICODE));
    }
}
