<?php
use \Yijin\Pay\Config\SxfConfig;
use \Yijin\Pay\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
### B扫C
//$config = (new Factory())->getConfig(Factory::PAY_BY_SXF);
//$config->channel = SxfConfig::$ALIPAY;
//$config->tradeNo = 'SB202012261548555';
//$config->totalAmount = 0.01;
//$config->subject = '起飞';
//$config->authCode = '123';
//$config->orgId = '85555555';
//$config->merchantNo = '866666666';
//$config->domain = 'https://openapi-test.tianquetech.com';
//$config->userIP = '127.0.0.1';
//$config->orgPrivateRSAKey = 'MII***==';
//
//$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
//$res = $payModel->barcodePay();
//var_dump($res);

### C扫B
//$config = (new Factory())->getConfig(Factory::PAY_BY_SXF);
//$config->channel = SxfConfig::$ALIPAY;
//$config->tradeNo = 'SB202012261548555';
//$config->totalAmount = 0.01;
//$config->subject = '起飞';
//$config->orgId = '85555555';
//$config->merchantNo = '866666666';
//$config->domain = 'https://openapi-test.tianquetech.com';
//$config->userIP = '127.0.0.1';
//$config->orgPrivateRSAKey = 'MII***==';

//$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
//$res = $payModel->qrcodePay();
//var_dump($res);

### 网页支付
$config = (new Factory())->getConfig(Factory::PAY_BY_SXF);
$config->channel = SxfConfig::$WE_PAY;
$config->tradeNo = 'SB202012261548555';
$config->totalAmount = 0.01;
$config->subject = '起飞';
$config->orgId = '855555';
$config->merchantNo = '39920100158****';
$config->userIP = '127.0.0.1';
$config->orgPrivateRSAKey = 'MIIC*==';
$config->orgPublicRSAKey = 'MIIBI*DAQAB';
$config->notifyUrl = 'https://www.abc.com/pay/notify';
$config->outFrontUrl = 'https://www.abc.com/pay/redirect';
$config->appid = 'wxcf09353c9f7****';
$config->isMiniProgram = 1;
$config->userId = 'oe2sbxKpzIHnt8tqNiK-*****';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->webPay();
var_dump($payModel->verifySign($res));
var_dump($res);

### 支付订单查询
//$config = (new Factory())->getConfig(Factory::PAY_BY_SXF);
//$config->channel = SxfConfig::$ALIPAY;
//$config->tradeNo = 'SB202012261548555';
//$config->orgId = '85555555';
//$config->merchantNo = '866666666';
//$config->domain = 'https://openapi-test.tianquetech.com';
//$config->orgPrivateRSAKey = 'MII***==';
//
//$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
//$res = $payModel->query();
//var_dump($res);

### 退款
//$config = (new Factory())->getConfig(Factory::PAY_BY_SXF);
//$config->channel = SxfConfig::$ALIPAY;
//$config->tradeNo = 'SB202012261548555';
//$config->refundTradeNo = 'SB-TK202012261548555';
//$config->totalAmount = 0.01;
//$config->orgId = '85555555';
//$config->merchantNo = '866666666';
//$config->domain = 'https://openapi-test.tianquetech.com';
//$config->orgPrivateRSAKey = 'MII***==';
//
//$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
//$res = $payModel->refund();
//var_dump($res);

### 退款订单查询
//$config = (new Factory())->getConfig(Factory::PAY_BY_SXF);
//$config->channel = SxfConfig::$ALIPAY;
//$config->refundTradeNo = 'SB-TK202012261548555';
//$config->orgId = '85555555';
//$config->merchantNo = '866666666';
//$config->domain = 'https://openapi-test.tianquetech.com';
//$config->orgPrivateRSAKey = 'MII***==';
//
//$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
//$res = $payModel->refundQuery();
//var_dump($res);
