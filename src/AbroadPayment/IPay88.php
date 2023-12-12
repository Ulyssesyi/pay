<?php
declare(strict_types=1);
namespace Yijin\Pay\AbroadPayment;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\AbroadConfig;
use Yijin\Pay\Enums\TransactionType;
use Yijin\Pay\Response;

class IPay88 extends Base
{
    use Response;
    const DOMAIN = 'https://payment.ipay88.com.my';
    private int $transType = 0;

    /**
     * @inheritDoc
     */
    function terminalPay(): array
    {
        return $this->error('暂不支持', -1);
    }
    /**
     * @inheritDoc
     */
    function barcodePay(): array
    {
        $params = [
            'ActionType' => '',
            'Amount' => number_format($this->config->totalAmount, 2, '.', ''),
            'BackendURL' => $this->config->notifyUrl,
            'BarcodeNo' => $this->config->authCode,
            'CCCId' => '',
            'CCCOriTokenId' => '',
            'CCMonth' => '',
            'CCName' => '',
            'CCNo' => '',
            'CCYear' => '',
            'CVV2' => '',
            'Currency' => 'MYR',
            'DiscountedAmount' => '',
            'MTLogId' => '',
            'MTVersion' => '',
            'MerchantCode' => $this->config->iPay88MerchantCode,
            'PaymentId' => 0,
            'ProdDesc' => 'Product Service',
            'PromoCode' => '',
            'RefNo' => $this->config->tradeNo,
            'Remark' => '',
        ];
        try {
            $params['Signature'] = $this->sign([TransactionType::BARCODE_PAY, $params]);
            $params = array_merge($params, [
                'SignatureType' => 'SHA256',
                'TerminalID' => '',
                'TokenID' => '',
                'UserContact' => $this->config->iPay88MerchantContact,
                'UserEmail' => $this->config->iPay88MerchantEmail,
                'UserName' => $this->config->iPay88MerchantName,
                'forexRate' => '',
                'lang' => 'UTF-8',
                'xField1' => '',
                'xField2' => '',
            ]);
            $res = $this->execRequest(TransactionType::BARCODE_PAY, $params);
            if (empty($res)) {
                return $this->error('请求失败', -1);
            }
            if (isset($res['s_Body']['EntryPageFunctionalityResponse']['EntryPageFunctionalityResult']['a_Status']) && $res['s_Body']['EntryPageFunctionalityResponse']['EntryPageFunctionalityResult']['a_Status'] == 1) {
                $trade_status = AbroadConfig::PAY_SUCCESS;
            } else {
                $trade_status = AbroadConfig::PAYING;
            }
            return $this->success(array_merge($res['s_Body'] ?? [], ['trade_status' => $trade_status]));
        } catch (Exception | GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function webPay(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function query(): array
    {
        $params = [
            'Amount' => number_format($this->config->totalAmount, 2, '.', ''),
            'MerchantCode' => $this->config->iPay88MerchantCode,
            'ReferenceNo' => $this->config->tradeNo,
        ];
        try {
            $res = $this->execRequest(TransactionType::QUERY, $params);
            if (empty($res)) {
                return $this->error('请求失败', -1);
            }
            if (isset($res['soap_Body']['TxDetailsInquiryCardInfoResponse']['TxDetailsInquiryCardInfoResult']['Status']) && $res['soap_Body']['TxDetailsInquiryCardInfoResponse']['TxDetailsInquiryCardInfoResult']['Status'] == 1) {
                $trade_status = AbroadConfig::PAY_SUCCESS;
            } else {
                $trade_status = AbroadConfig::PAYING;
            }
            return $this->success(array_merge($res['soap_Body'] ?? [], ['trade_status' => $trade_status]));
        } catch (Exception | GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function refund(): array
    {
        $params = [
            'amount' => number_format($this->config->totalAmount, 2, '.', ''),
            'cctransid' => $this->config->tradeNo,
            'currency' => 'MYR',
            'merchantcode' => $this->config->iPay88MerchantCode,
        ];
        try {
            $params['signature'] = $this->sign([TransactionType::REFUND, $params]);
            $res = $this->execRequest(TransactionType::REFUND, $params);
            if (empty($res)) {
                return $this->error('请求失败', -1);
            }
            if (isset($res['soap_Body']['VoidTransactionResponse']['VoidTransactionResult']) && $res['soap_Body']['VoidTransactionResponse']['VoidTransactionResult'] == 0) {
                $refund_status = AbroadConfig::REFUND_SUCCESS;
            } else {
                $refund_status = AbroadConfig::REFUND_FAIL;
            }
            return $this->success(array_merge($res['soap_Body'] ?? [], ['refund_status' => $refund_status]));
        } catch (Exception | GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function notify($data): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function notifySuccess(array $params = []): string
    {
        return 'success';
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    function sign($data): string
    {
        list($type, $params) = $data;
        $linkString = $this->config->iPay88MerchantKey;
        if ($type === TransactionType::BARCODE_PAY) {
            if (isset($params['MerchantCode'])) {
                $linkString .= $params['MerchantCode'];
            }
            if (isset($params['RefNo'])) {
                $linkString .= $params['RefNo'];
            }
            if (isset($params['Amount'])) {
                $linkString .= str_replace(['.', ','], '', (string)$params['Amount']);
            }
            if (isset($params['Currency'])) {
                $linkString .= $params['Currency'];
            }
            if (isset($params['BarcodeNo'])) {
                $linkString .= $params['BarcodeNo'];
            }
            if (isset($params['TerminalID'])) {
                $linkString .= $params['TerminalID'];
            }
            return hash('sha256', $linkString);
        } else if ($type === TransactionType::REFUND) {
            if (isset($params['merchantcode'])) {
                $linkString .= $params['merchantcode'];
            }
            if (isset($params['cctransid'])) {
                $linkString .= $params['cctransid'];
            }
            if (isset($params['amount'])) {
                $linkString .= str_replace(['.', ','], '', (string)$params['amount']);
            }
            if (isset($params['currency'])) {
                $linkString .= $params['currency'];
            }
            return base64_encode(hash('sha1', $linkString, true));
        } else {
            throw new Exception('未知的交易类型', -1);
        }
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        return false;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function execRequest(int $type, $params)
    {
        $this->transType = $type;
        $client = new Client([
            'base_uri' => self::DOMAIN,
        ]);
        $res = $client->post($this->getUrl(), [
            'body' => $this->buildXMLParams($params),
            'headers' => [
                'Content-Type' => 'text/xml',
                'SOAPAction' => $this->getSoapAction(),
            ]
        ]);
        $content = $res->getBody()->getContents();
        if ($content) {
            $obj = SimpleXML_Load_String($content);
            if ($obj === FALSE) return [];

            // GET NAMESPACES, IF ANY
            $nss = $obj->getNamespaces(TRUE);
            if (empty($nss)) return [];

            // CHANGE ns: INTO ns_
            $nsm = array_keys($nss);
            foreach ($nsm as $key)
            {
                // A REGULAR EXPRESSION TO MUNG THE XML
                $rgx
                    = '#'               // REGEX DELIMITER
                    . '('               // GROUP PATTERN 1
                    . '<'              // LOCATE A LEFT WICKET
                    . '/?'              // MAYBE FOLLOWED BY A SLASH
                    . preg_quote($key)  // THE NAMESPACE
                    . ')'               // END GROUP PATTERN
                    . '('               // GROUP PATTERN 2
                    . ':{1}'            // A COLON (EXACTLY ONE)
                    . ')'               // END GROUP PATTERN
                    . '#'               // REGEX DELIMITER
                ;
                // INSERT THE UNDERSCORE INTO THE TAG NAME
                $rep
                    = '$1'          // BACKREFERENCE TO GROUP 1
                    . '_'           // LITERAL UNDERSCORE IN PLACE OF GROUP 2
                ;
                // PERFORM THE REPLACEMENT
                $content =  preg_replace($rgx, $rep, $content);
            }
            return json_decode(json_encode(SimpleXML_Load_String($content, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        }
        return [];
    }

    private function buildXMLParams(array $params): string
    {
        switch ($this->transType === TransactionType::BARCODE_PAY) {
            case TransactionType::BARCODE_PAY:
                $startTag = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mob="https://www.mobile88.com" xmlns:mhp="http://schemas.datacontract.org/2004/07/MHPHGatewayService.Model"><soapenv:Header/><soapenv:Body><mob:EntryPageFunctionality><mob:requestModelObj>';
                $endTag = "</mob:requestModelObj> </mob:EntryPageFunctionality></soapenv:Body></soapenv:Envelope>";
                $prefix = 'mhp';
                break;
            case TransactionType::QUERY:
                $startTag = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="https://www.mobile88.com/epayment/webservice"><soapenv:Header/><soapenv:Body><web:TxDetailsInquiryCardInfo>';
                $endTag = "</web:TxDetailsInquiryCardInfo></soapenv:Body></soapenv:Envelope>";
                $prefix = 'web';
                break;
            case TransactionType::REFUND:
                $startTag = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:mob="https://www.mobile88.com"><soap:Header/><soap:Body><mob:VoidTransaction>';
                $endTag = "</mob:VoidTransaction></soap:Body></soap:Envelope>";
                $prefix = 'mob';
                break;
            default:
                throw new Exception('未知的交易类型', -1);
        }
        $xml = $startTag;
        foreach ($params as $key => $value) {
            $value = htmlspecialchars($value, ENT_XML1);
            if($prefix!=''){
                $xml .= '<'.$prefix.':' . $key . '>' . $value . '</' .$prefix.':'. $key . '>';
            }else{
                $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
            }
        }
        $xml .= $endTag;
        return $xml;
    }

    private function getUrl(): string
    {
        switch ($this->transType) {
            case TransactionType::BARCODE_PAY:
                return '/ePayment/WebService/MHGatewayService/GatewayService.svc';
            case TransactionType::QUERY:
                return '/ePayment/Webservice/TxInquiryCardDetails/TxDetailsInquiry.asmx';
            case TransactionType::REFUND:
                return '/epayment/webservice/voidapi/voidfunction.asmx';
            default:
                return '';
        }
    }

    private function getSoapAction():string
    {
        switch ($this->transType) {
            case TransactionType::BARCODE_PAY:
                return 'https://www.mobile88.com/IGatewayService/EntryPageFunctionality';
            case TransactionType::QUERY:
                return 'https://www.mobile88.com/epayment/webservice/TxDetailsInquiryCardInfo';
            case TransactionType::REFUND:
                return 'https://www.mobile88.com/VoidTransaction';
            default:
                return '';
        }
    }
}
