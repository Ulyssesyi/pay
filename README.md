# 介绍
这个项目初衷是适配公司多个不同支付渠道下（支付宝/微信支付）的支付组件，目前支持的支付渠道如下

| 渠道   | 渠道编号 | 进度                                           |
|------|------|----------------------------------------------|
| 官方   | 5    | （<font color=green>已完成</font>）               |
| 随行付  | 10   | （<font color=green>已完成</font>）               |
| 联付通  | 1    | （<font color=green>已完成</font>）               |
| 付呗   | 4    | （<font color=green>已完成</font>）               |
| 收钱吧  | 6    | （<font color=green>已完成</font>）               |
| 乐刷   | 7    | （<font color=green>已完成</font>）               |
| 云闪付  | 8    | （<font color=red>待完成</font>）                 |
| 乐天成  | 11   | （<font color=green>已完成，未测试，不成熟，不建议接入</font>） |
| 移动杭研 | 12   | （<font color=red>待完成</font>）                 |

# 更多
可以通过继承Base类后实现更多的支付渠道

# 示例
参看examples文件夹

# 请求参数
## 通用参数

| 名称            | 是否必填 | 类型     | 示例                             | 说明                                                            |
|---------------|------|--------|--------------------------------|---------------------------------------------------------------|
| channel       | M    | int    | 1                              | 支付渠道 1-联付通，4-付呗，5-官方直连，6-收钱吧，7-乐刷，8-云闪付，10-随行付，11-乐天成         |
| payType       | M    | int    | 1                              | 支付通道。1-支付宝，2-微信，3-银联，暂未支持银联，只是预留拓展                            |
| charset       | C    | string | UTF-8                          | 请求和返回编码，目前都是UTF-8                                             |
| tradeNo       | M    | string | SB202012261548555              | 商户订单号                                                         |
| refundTradeNo | O    | string | SBTK202012261548555            | 商户退款订单号                                                       |
| totalAmount   | O    | float  | 1.23                           | 订单总金额/退款总金额，可多次退款的渠道可能需要同时传入订单金额和退款金额，目前不支持传入不同值，即只能全退        |
| notifyUrl     | O    | string | https://www.abc.com/pay/notify | 支付结果异步通知地址，网页/小程序支付必填                                         |
| subject       | C    | string | FPX.Doinb                      | 订单标题                                                          |
| authCode      | O    | string | 12331231321                    | B扫C时读取到的条码内容                                                  |
| appid         | O    | string | wx5ccf1abe464a2215             | 微信支付时发起支付的公众号/小程序的APP ID                                      |
| isMiniProgram | O    | int    | 1                              | webPay是不是由小程序发起，1-小程序，0-公众号/服务窗/js支付                          |
| userId        | O    | string | oDdgAwTnZ2z4ov8p-VDAb-0GeBIU   | 用户在微信/支付宝中的id，即微信的openid，支付宝的buyer_id .etc                    |
| userIP        | O    | string | 192.168.1.1                    | 用户发起请求的IP地址，目前只有微信直连支付和随行付支付需要传入                              |
| optional      | O    | array  | ['a'=>1]                       | 用于更多未添加的参数，当前只写了最小需求的参数，如果有更多需传给第三方的参数，可以通过该数组传入，具体参数请查阅第三方文档 |

## 支付宝直连参数
| 名称                 | 是否必填 | 类型     | 示例                                         | 说明                                     |
|--------------------|------|--------|--------------------------------------------|----------------------------------------|
| appAuthToken       | O    | string | Sf*****                                    | ISV服务商模式下的授权token，不填写就是商户直连，填写就是走服务商支付 |
| merchantPrivateKey | M    | string | MII****                                    | 应用私钥                                   |
| alipayCertPath     | O    | string | /foo/alipayCertPublicKey_RSA2.crt          | 支付宝公钥证书文件路径                            |
| alipayRootCertPath | O    | string | /foo/alipayRootCert.crt                    | 支付宝根证书文件路径                             |
| merchantCertPath   | O    | string | /foo/appCertPublicKey_2019051064521003.crt | 应用公钥证书文件路径                             |
| alipayPublicKey    | O    | string | MII*****                                   | 支付宝公钥，非证书模式填入这个，此时不需要上面三个证书            |
| encryptKey         | C    | string | MII*****                                   | AES密钥，调用AES加解密相关接口时需要，非必填              |

## 微信直连参数(V2版本)
| 名称                      | 是否必填 | 类型     | 示例                       | 说明                       |
|-------------------------|------|--------|--------------------------|--------------------------|
| mchId                   | M    | string | 85555555                 | 商户号                      |
| subAppId                | O    | string | wx*****                  | 子商户的公众号/小程序的APP ID       |
| subMchId                | O    | string | 85555555                 | 子商户号，填写代表上面的商户号是服务商      |
| apiV2Key                | M    | string | vdZV***                  | 商户API v2密钥               |
| clientApiV2KeyFilePath  | O    | string | /foo/api_client_key.pem  | 商户API v2证书密钥地址，退款等接口需要证书 |
| clientApiV2CertFilePath | O    | string | /foo/api_client_cert.pem | 商户API v2证书地址，退款等接口需要证书   |
| attach                  | C    | string | 'a:1'                    | 附加数据，不建议使用               |
| expireTime              | C    | int    | 123131                   | 订单有效截止10位（秒级）时间戳         |

## 随行付参数
| 名称                  | 是否必填 | 类型     | 示例                               | 说明                        |
|---------------------|------|--------|----------------------------------|---------------------------|
| orgIdSxf            | M    | string | 85555555                         | 服务商编号                     |
| merchantNoSxf       | M    | string | 85555555                         | 商户编号                      |
| orgPrivateRSAKeySxf | M    | string | MIB***                           | 服务商RSA私钥内容                |
| orgPublicRSAKeySxf  | M    | string | MIB***                           | 平台RSA公钥内容                 |
| outFrontUrlSxf      | O    | string | https://www.abc.com/pay/redirect | web支付后跳转网页地址              |
| wechatFoodOrderSxf  | C    | string | FoodOrder                        | 微信扫码点餐标识，目前仅有FoodOrder可上传 |
| refundReasonSxf     | C    | string | 商家与消费者协商一致                       | 退款原因。默认值：商家与消费者协商一致       |

## 联付通参数
| 名称             | 是否必填 | 类型     | 示例         | 说明                         |
|----------------|------|--------|------------|----------------------------|
| userNameLT     | O    | string | 85555555   | 商户后台登录账号，用于auth方法获取商户的支付信息 |
| userPwdLt      | O    | string | 85555555   | 商户后台登录密码，用于auth方法获取商户的支付信息 |
| appIdLt        | M    | string | EW_***     | 合作方ID，通过auth方法获得           |
| appKeyLt       | M    | string | 8cc***     | 签名密钥，通过auth方法获得            |
| merchantCodeLt | M    | string | EW_***     | 商户编号，通过auth方法获得            |
| refundReasonLt | C    | string | 商家与消费者协商一致 | 退款原因。默认值：商家与消费者协商一致        |

## 付呗参数
| 名称            | 是否必填 | 类型     | 示例      | 说明                           |
|---------------|------|--------|---------|------------------------------|
| merchantIdFb  | M    | string | 2021*** | 商户ID                         |
| merchantKeyFb | M    | string | 3b2***  | 商户密码                         |
| storeIdFb     | M    | string | 11***   | 商户门店ID                       |
| wxOpenIDFb    | O    | string | wx***   | 付呗微信网页支付下需要的openid，获取方法见付呗文档 |

## 收钱吧参数
| 名称                   | 是否必填 | 类型     | 示例                               | 说明                                 |
|----------------------|------|--------|----------------------------------|------------------------------------|
| serviceProviderIDSqb | O    | string | 2021***                          | 服务商ID，用于激活获取终端码                    |
| activateCodeSqb      | O    | string | 2311***                          | 激活码，用于激活获取终端码                      |
| activateDeviceIDSqb  | O    | string | 123***                           | 激活设备ID，用于激活获取终端码                   |
| terminalSNSqb        | M    | string | xx***                            | 终端码，通过activate接口激活获取               |
| terminalKeySqb       | M    | string | xx***                            | 终端密钥，通过activate接口激活获取或者checkin接口刷新 |
| returnUrlSqb         | C    | string | https://www.abc.com/pay/redirect | web支付后跳转网页地址                       |
| reflectSqb           | C    | string | 'a:1'                            | web支付后的反射参数                        |
| operatorSqb          | C    | string | Obama***                         | 操作员,好像没啥用                          |

## 乐刷参数
| 名称                   | 是否必填 | 类型     | 示例                               | 说明             |
|----------------------|------|--------|----------------------------------|----------------|
| merchantIdLS         | M    | string | 2021***                          | 商户ID           |
| serviceProviderKeyLS | M    | string | 2311***                          | 服务商密码          |
| jumpUrlLS            | O    | string | https://www.abc.com/pay/redirect | 使用乐刷收银台支付后跳回地址 |

## 乐天成支付参数
| 名称               | 是否必填 | 类型     | 示例                               | 说明               |
|------------------|------|--------|----------------------------------|------------------|
| appKeyLtc        | M    | string | zzxx***                          | 商户在乐天成的支付PayCode |
| privateSecretLtc | M    | string | MB****                           | 商户在乐天成的私钥        |
| publicSecretLtc  | M    | string | MB*****                          | 乐天成的公钥           |
| requestDomainLtc | M    | string | https://www.abc.com/pay/redirect | 乐天成的支付请求地址       |
| jumpUrlLtc       | O    | string | https://www.abc.com/pay/redirect | 使用乐天成收银台支付后跳回地址  |
| accessSecretLtc  | C    | string | MB***                            | 商户在乐天成的内容密钥      |

## 移动杭研支付参数
| 名称             | 是否必填 | 类型     | 示例           | 说明             |
|----------------|------|--------|--------------|----------------|
| domainHY       | M    | string | https://xxxx | 移动杭研支付请求域名     |
| originIdHY     | M    | string | 2021***      | 移动杭研的交易来源Id    |
| merchantCodeHY | M    | string | 189****1234  | 商户在移动杭研的商户code |
| merchantIdHY   | M    | int    | 123          | 商户在移动杭研的商户id   |
| productIdHY    | M    | int    | 123          | 移动杭研的产品id      |
| privateKeyHY   | M    | string | MB***        | 商户在移动杭研的密钥     |
| publicKeyHY    | M    | string | MB***        | 移动杭研的公钥        |

# 返回参数
## 通用参数

| 名称       | 是否必填 | 类型     | 示例             | 说明                                  |
|----------|------|--------|----------------|-------------------------------------|
| result   | M    | bool   | true           | 支付请求结果，true-请求成功，false-请求失败         |
| errMsgNo | C    | mixed  | 1001           | 支付请求失败的失败错误码，用于特定场景的特殊处理            |
| errMsg   | C    | string | 缺失参数xxx        | 支付请求失败的失败原因                         |
| data     | C    | mixed  | https://xxx/xx | 请求成功时，一些额外信息返回，各接口的返回必需参数参看Base类的注释 |

# 名词解释
M-必填，C-可以不填写，O-部分场景下必填
