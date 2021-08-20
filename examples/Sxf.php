<?php
use \Yijin\Pay\Config\SxfConfig;
use \Yijin\Pay\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
### B扫C
$config = new SxfConfig();
$config->channel = SxfConfig::$ALIPAY;
$config->tradeNo = 'SB202012261548555';
$config->totalAmount = 0.01;
$config->subject = '起飞';
$config->authCode = '123';
$config->orgId = '85555555';
$config->merchantNo = '866666666';
$config->domain = 'https://openapi-test.tianquetech.com';
$config->userIP = '127.0.0.1';
$config->orgPrivateRSAKey = 'MII***==';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->barcodePay();
var_dump($res);

### C扫B
$config = new SxfConfig();
$config->channel = SxfConfig::$ALIPAY;
$config->tradeNo = 'SB202012261548555';
$config->totalAmount = 0.01;
$config->subject = '起飞';
$config->orgId = '85555555';
$config->merchantNo = '866666666';
$config->domain = 'https://openapi-test.tianquetech.com';
$config->userIP = '127.0.0.1';
$config->orgPrivateRSAKey = 'MII***==';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->qrcodePay();
var_dump($res);

### 网页支付
$config = new SxfConfig();
$config->channel = SxfConfig::$ALIPAY;
$config->tradeNo = 'SB202012261548555';
$config->totalAmount = 0.01;
$config->subject = '起飞';
$config->orgId = '85555555';
$config->merchantNo = '866666666';
$config->domain = 'https://openapi-test.tianquetech.com';
$config->userIP = '127.0.0.1';
$config->orgPrivateRSAKey = 'MII***==';
$config->notifyUrl = 'https://www.abc.com/pay/notify';
$config->outFrontUrl = 'https://www.abc.com/pay/redirect';
$config->appid = 'wx5ccf1abe464a2215';
$config->isMiniProgram = 0;
$config->userId = 'oDdgAwTnZ2z4ov8p-VDAb-0GeBIU';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->webPay();
var_dump($res);

### 支付订单查询
$config = new SxfConfig();
$config->channel = SxfConfig::$ALIPAY;
$config->tradeNo = 'SB202012261548555';
$config->orgId = '85555555';
$config->merchantNo = '866666666';
$config->domain = 'https://openapi-test.tianquetech.com';
$config->orgPrivateRSAKey = 'MII***==';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->query();
var_dump($res);

### 退款
$config = new SxfConfig();
$config->channel = SxfConfig::$ALIPAY;
$config->tradeNo = 'SB202012261548555';
$config->refundTradeNo = 'SB-TK202012261548555';
$config->totalAmount = 0.01;
$config->orgId = '85555555';
$config->merchantNo = '866666666';
$config->domain = 'https://openapi-test.tianquetech.com';
$config->orgPrivateRSAKey = 'MII***==';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->refund();
var_dump($res);

### 退款订单查询
$config = new SxfConfig();
$config->channel = SxfConfig::$ALIPAY;
$config->refundTradeNo = 'SB-TK202012261548555';
$config->orgId = '85555555';
$config->merchantNo = '866666666';
$config->domain = 'https://openapi-test.tianquetech.com';
$config->orgPrivateRSAKey = 'MII***==';

$payModel = (new Factory())->getAdapter(Factory::PAY_BY_SXF, $config);
$res = $payModel->refundQuery();
var_dump($res);
