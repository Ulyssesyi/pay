# 介绍
这个项目初衷是适配公司多个不同支付渠道下（支付宝/微信支付）的支付组件，目前支持的支付渠道如下

渠道|渠道编号|进度
---|---|---
官方|5|（<font color=red>待完成</font>）
随行付|10|（<font color=green>已完成</font>）
联付通|1|（<font color=red>待完成</font>）
付呗|4|（<font color=red>待完成</font>）
收钱吧|6|（<font color=red>待完成</font>）
乐刷|7|（<font color=red>待完成</font>）
云闪付|8|（<font color=red>待完成</font>）
乐天成|11|（<font color=red>待完成</font>）

# 更多
可以通过继承Base类后实现更多的支付渠道

# 示例
参看examples文件夹

# 请求参数
## 通用参数

名称|是否必填|类型|示例|说明
---|---|---|---|---
channel|M|int|1|支付通道。1-支付宝，2-微信，3-银联
charset|C|string|UTF-8|请求和返回编码，目前都是UTF-8
tradeNo|M|string|SB202012261548555|商户订单号
refundTradeNo|O|string|SBTK202012261548555|商户退款订单号
notifyUrl|O|string|https://www.abc.com/pay/notify|支付结果异步通知地址，webPay必填
totalAmount|O|float|1.23|订单总金额，只有查询订单不需要填写
subject|C|string|FPX.Doinb|订单标题
authCode|O|string|12331231321|B扫C时读取到的条码内容
appid|O|string|wx5ccf1abe464a2215|微信支付时发起支付的公众号/小程序的APP ID
isMiniProgram|O|int|1|webPay是不是由小程序发起，1-小程序，0-公众号/服务窗/js支付
userId|O|string|oDdgAwTnZ2z4ov8p-VDAb-0GeBIU|用户在微信/支付宝中的id，即微信的openid，支付宝的buyer_id .etc

## 随行付参数
名称|是否必填|类型|示例|说明
---|---|---|---|---
orgId|M|string|85555555|机构/服务商编号
merchantNo|M|string|85555555|商户编号
domain|M|string|https://openapi-test.tianquetech.com|接口地址
userIP|O|string|127.0.0.1|商户端请求IP地址
orgPrivateRSAKey|M|string|MII***==|服务商RSA私钥内容
outFrontUrl|O|string|https://www.abc.com/pay/redirect|H5支付后跳转网页地址
wechatFoodOrder|C|string|FoodOrder|微信扫码点餐标识，目前仅有FoodOrder可上传
refundReason|C|string|商家与消费者协商一致|退款原因。默认值：商家与消费者协商一致

更多参数目前用不到没加上，需要的可以自己添加

# 返回参数
## 通用参数

名称|是否必填|类型|示例|说明
---|---|---|---|---
result|M|bool|true|支付请求结果，true-请求成功，false-请求失败
errMsgNo|C|mixed|1001|支付请求失败的失败错误码，用于特定场景的特殊处理
errMsg|C|string|缺失参数xxx|支付请求失败的失败原因
data|C|mixed|https://xxx/xx|支付请求成功时，一些额外信息返回

# 名词解释
M-必填，C-可以不填写，O-部分场景下必填
