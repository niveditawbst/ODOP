<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Controller;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

abstract class Worldline extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    // Local constants
    const API_CONTROLLER_PATH = 'worldline/api/';
    const PUSH_CONTROLLER_PATH = 'worldline/push/';

    const INTERFACE_VERSION = 'HP_2.3';
    const STATUS_SUCCESS = '00';
    const STATUS_PENDING = '60';
    const STATUS_TIMEOUT = '97';

    // Default order statuses
    const DEFAULT_STATUS_PENDING = 'pending';
    const DEFAULT_STATUS_PENDING_PAYMENT = 'pending_payment';
    const DEFAULT_STATUS_PROCESSING = 'processing';

    protected $log;

    /**
     * @var \Appmerce\Worldline\Model\Worldline
     */
    protected $api;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $log
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Appmerce\Worldline\Model\Worldline $api
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $log,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Appmerce\Worldline\Model\Worldline $api,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->log = $log;
        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
        $this->api = $api;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    
    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_objectManager->get('Magento\Quote\Model\Quote');
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        return $this->_objectManager->get('Magento\Sales\Model\Order');
    }

    /**
     * Get store configuration
     */
    public function getPaymentConfigData($code, $field, $storeId = null)
    {
        $path = 'payment/' . $code . '/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getServiceConfigData($field, $storeId = null)
    {
        $path = 'payment/appmerce_worldline_shared/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    public function getGatewayUrl($order)
    {
        $storeId = $order->getStoreId();

        $gateways = array(
            '0' => 'https://ipg.in.worldline.com/doMEPayRequest', //producttion url
            '1' => 'https://cgt.in.worldline.com/ipg/doMEPayRequest' //test url
        );

        $testMode = $this->getServiceConfigData('test_mode');
        return $gateways[$testMode];
    }

    /**
     * Decide currency code type
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        if ($this->getServiceConfigData('base_currency')) {
            $currencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        }
        else {
            $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        }
        return $currencyCode;
    }

    /**
     * Get language code (iso-2)
     */
    public function getLanguageCode()
    {
        $locale = $this->localeResolver->getLocale();
        $lang = substr($locale, 0, 2);
        if (!in_array($lang, array(
            'en',
            'nl',
            'fr',
            'es',
            'de',
        ))) {
            $lang = 'en';
        }
        return $lang;
    }

    /**
     * Decide amount base or store
     *
     * @param $order
     * @param $currencyCode string
     * @return string
     */
    public function _getGrandTotal($order, $currencyCode)
    {
        if ($this->getServiceConfigData('base_currency')) {
            $amount = $order->getBaseGrandTotal();
        }
        else {
            $amount = $order->getGrandTotal();
        }

        // Currency JPY has no decimals. Worldline does not multiply * 100.
        switch ($currencyCode) {
            case 'JPY' :
                $amount = round($amount);
                break;
            default :
                $amount = round($amount * 100);
        }
        return $amount;
    }

    /**
     * Generates array of fields for redirect form
     *
     * @param string $code
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    //~ public function getPostData($order)
    //~ {
        //~ $storeId = $order->getStoreId();
        //~ $address = $order->getBillingAddress();
        //~ $paymentMethodCode = $order->getPayment()->getMethod();
        //~ $paymentMean = $this->getPaymentMean($paymentMethodCode);
        //~ $customerId = $order->getCustomerId();
        //~ $secretKey = $this->getServiceConfigData('secret_key', $storeId);
        //~ $currencyCode = $this->getCurrencyCode();
        
        //~ // Prepare data fields
        //~ $data = array();
        //~ $data['currencyCode'] = $this->getCurrencyDigits($currencyCode);
        //~ $data['merchantId'] = $this->getServiceConfigData('merchant_id', $storeId);
        //~ $data['normalReturnUrl'] = preg_replace('/\?.*/', '', $this->getApiUrl('returnUser', $storeId));
        //~ $data['amount'] = $this->_getGrandTotal($order, $currencyCode);
        //~ // Make reference unique with REQUEST_TIME to prevent blocked transactions
        //~ $data['transactionReference'] = $order->getId() . time();
        //~ $data['keyVersion'] = $this->getServiceConfigData('key_version', $storeId);
        //~ $data['automaticResponseUrl'] = preg_replace('/\?.*/', '', $this->getPushUrl('response', $storeId));
        //~ #captureDay
        //~ #captureMode
        //~ #customerId
        //~ $data['customerIpAddress'] = $this->getRealIpAddress();
        //~ $data['customerLanguage'] = $this->getLanguageCode();
        //~ #expirationDate
        //~ #hashSalt1
        //~ #hashSalt2
        //~ #invoiceReference
        //~ #merchantSessionId
        //~ #merchantTransactionDateTime
        //~ #merchantWalletID
        //~ #orderChannel
        //~ $data['orderId'] = $order->getIncrementId();
        //~ $data['paymentMeanBrandList'] = $paymentMean['brand'];
        //~ #paymentPattern
        //~ #returnContext
        //~ #statementReference
        //~ #templateName
        //~ #transactionActors
        //~ #transactionOrigin
        
        //~ // Optional fields related to fraud
        //~ #fraudData.allowedCardArea
        //~ #fraudData.allowedCardCountryList
        //~ #fraudData.allowedIpArea
        //~ #fraudData.allowedIpCountryList
        //~ #fraudData.bypass3DS
        //~ #fraudData.bypassCtrlList
        //~ #fraudData.bypassInfoList
        //~ #fraudData.deniedCardArea
        //~ #fraudData.deniedCardCountryList
        //~ #fraudData.deniedIpArea
        //~ #fraudData.deniedIpCountryList
        
        //~ // Optional fields relating to payment pages
        //~ $data['paypageData.bypassReceiptPage'] = 'Y';
        
        //~ // Optional fields relating to payment methods
        //~ // For PayPal
        //~ #paymentMeanData.paypal.landingPage
        //~ #paymentMeanData.paypal.addrOVerride
        //~ #paymentMeanData.paypal.invoiceId
        //~ #paymentMeanData.paypal.dupFlag
        //~ #paymentMeanData.paypal.dupDesc
        //~ #paymentMeanData.paypal.dupCustom
        //~ #paymentMeanData.paypal.dupType
        //~ #paymentMeanData.paypal.mobile

        //~ // For SDD
        //~ #paymentMeanData.sdd.mandateAuthentMethod
        //~ #paymentMeanData.sdd.mandateUsage

        //~ // Optional fields for payment in N installments
        //~ #installmentData.number
        //~ #installmentData.datesList
        //~ #installmentData.transactionReferencesList
        //~ #installmentData.amountsList
        
        //~ // Construct datastring
        //~ $dataString = array();
        //~ foreach ($data as $field => $value) {
            //~ $dataString[] = $field . '=' . $value;
        //~ }
        
        //~ // Prepare post fields
        //~ $fields = array();
        //~ $fields[] = array('name' => 'Data', 'value' => implode('|', $dataString));
        //~ $fields[] = array('name' => 'InterfaceVersion', 'value' => self::INTERFACE_VERSION);
        //~ $fields[] = array('name' => 'Seal', 'value' => hash('sha256', implode('|', $dataString) . $secretKey));
        //~ return $fields;
    //~ }

    public function getPostData($order)
    {
        $storeId = $order->getStoreId();
        $address = $order->getBillingAddress();
        $paymentMethodCode = $order->getPayment()->getMethod();
        $paymentMean = $this->getPaymentMean($paymentMethodCode);
        $customerId = $order->getCustomerId();
        $secretKey = $this->getServiceConfigData('secret_key', $storeId);
        //~ $secretKey = '6375b97b954b37f956966977e5753ee6';
        $currencyCode = $this->getCurrencyCode();
      
        $mId = $this->getServiceConfigData('merchant_id', $storeId);
        //~ $mId = 'WL0000000027698';
		$trnAmt = number_format($order->getBaseGrandTotal() * 100, 0, '.', '');
		$orderId = $order->getIncrementId().'-'.time();
		$trnCurrency = $currencyCode;
		$trnRemarks =  'Order Payment' ;
		$meTransReqType = 'S';
		$recurrPeriod = '';
		$recurrDay = '';
		$noOfRecurring = '';
		$responseUrl = preg_replace('/\?.*/', '', $this->getApiUrl('returnUser', $storeId));
		$addField1 = '';
		$addField2 = '';
		$addField3 = '';
		$addField4 = '';
		$addField5 = '';
		$addField6 = '';
		$addField7 = '';
		$addField8 = '';
		$addField9 = 'NA';
		$addField10 = 'NA';
		
		$message =  $mId  . "|" . $orderId  . "|" . $trnAmt  . "|" . 
			$trnCurrency  . "|" . $trnRemarks  . "|" . $meTransReqType  . "|" . 
			$recurrPeriod  . "|" . $recurrDay  . "|" . $noOfRecurring  . "|" . 
			$responseUrl  . "|" . $addField1  . "|" . $addField2  . "|" . 
			$addField3  . "|" . $addField4  . "|" . $addField5  . "|" . 
			$addField6  . "|" . $addField7  . "|" . $addField8  . "|" . 
			$addField9  . "|" . $addField10 ;
			
			
		//~ echo '======'.$message;exit;
		return $this->encryptValue( $message, $secretKey );	

    }
    
    function encryptValue($inputVal, $secureKey) {
		$key = '';
		for($i = 0; $i < strlen ( $secureKey ) - 1; $i += 2) {
			$key .= chr ( hexdec ( $secureKey [$i] . $secureKey [$i + 1] ) );
		}
		
		$block = mcrypt_get_block_size ( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB );
		$pad = $block - (strlen ( $inputVal ) % $block);
		$inputVal .= str_repeat ( chr ( $pad ), $pad );
		
		$encrypted_text = bin2hex ( mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $key, $inputVal, MCRYPT_MODE_ECB ) );
		
		return $encrypted_text;
	}

    /**
     * Get customer IP address
     * 
     * @return ipaddress
     */
    public function getRealIpAddress() {
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		$a = $om->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
		return $a->getRemoteAddress();
    }
    
    /**
     * Get data bits
     *
     * @param array Data string "key=value|key=value|key=value"
     *
     * @return array Data bits as array
     */
    public function getDataBits($data)
    {
        $dataBytes = explode('|', $data);
        $dataBits = array();
        foreach ($dataBytes as $byte) {
            $explode = explode('=', $byte);
            $dataBits[$explode[0]] = $explode[1];
        }
        return $dataBits;
    }

    /**
     * Check seal
     *
     * @param $params array Request parameters
     * @param $order
     *
     * @return boolean
     */
    public function checkSeal($params, $order)
    {
        $secretKey = $this->getServiceConfigData('secret_key', $order->getStoreId());
        return (hash('sha256', $params['Data'] . $secretKey) == $params['Seal']);
    }

    /**
     * Get response text
     *
     * @param $code string
     * @return string Untranslated response message
     */
    public function getResponseText($code)
    {
        $responses = array(
            '00' => 'Transaction success, authorization accepted.',
            '02' => 'Authorization limit exceeded.',
            '03' => 'Invalid merchant contract.',
            '05' => 'Authorization refused.',
            '12' => 'Invalid transaction parameters.',
            '14' => 'Invalid card, security code or validation value.',
            '17' => 'Cancelled by user.',
            '24' => 'Invalid status.',
            '25' => 'Transaction not found.',
            '30' => 'Invalid format.',
            '34' => 'Fraud suspicion.',
            '40' => 'Operation not allowed.',
            '60' => 'Pending transaction.',
            '63' => 'Security breach.',
            '75' => 'Maximum attempts exceeded.',
            '90' => 'Acquirer server unavailable.',
            '94' => 'Duplicate transaction.',
            '97' => 'Request time-out.',
            '99' => 'Payment page unavailable.',
        );

        return array_key_exists($code, $responses) ? $responses[$code] : 'Payment failed. Please try again.';
    }

    /**
     * Get special currency code
     */
    public function getCurrencyDigits($iso3)
    {
        $currencies = array(
            'EUR' => '978',
            'USD' => '840',
            'CHF' => '756',
            'GBP' => '826',
            'CAD' => '124',
            'JPY' => '392',
            'AUD' => '036',
            'NOK' => '578',
            'SEK' => '752',
            'DKK' => '208',
            //~ 'INR' => '208',
        );

        return $currencies[$iso3];
    }

    /**
     * Get payment mean brand
     */
    public function getPaymentMean($code)
    {
        $methods = array(
            'appmerce_worldline_ideal' => array(
                'brand' => 'IDEAL',
                'type' => 'CREDIT_TRANSFER'
            ),
            'appmerce_worldline_visa' => array(
                'brand' => 'VISA',
                'type' => 'CARD'
            ),
            'appmerce_worldline_mastercard' => array(
                'brand' => 'MASTERCARD',
                'type' => 'CARD'
            ),
            'appmerce_worldline_maestro' => array(
                'brand' => 'MAESTRO',
                'type' => 'CARD'
            ),
            'appmerce_worldline_bcmc' => array(
                'brand' => 'BCMC',
                'type' => 'CARD'
            ),
            'appmerce_worldline_vpay' => array(
                'brand' => 'VPAY',
                'type' => 'CARD'
            ),
        );

        return $methods[$code];
    }

    /**
     * Return redirect URL for method
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function getJsonData($order)
    {
		$storeId = $order->getStoreId();
		$postField[0]['name'] = 'merchantRequest';
		$postField[0]['value'] = $this->getPostData($order);
		$postField[1]['name'] = 'MID';
		$postField[1]['value'] = $this->getServiceConfigData('merchant_id', $storeId);
		
        return array('url' => $this->getGatewayUrl($order), 'fields' => $postField);
    }
    
    /**
     * Return URLs
     * 
     * @param string $key
     * @param int $storeId
     * @param bool $noSid
     * @return mixed
     */
    public function getApiUrl($key, $storeId = null, $noSid = false)
    {
        return $this->_url->getUrl(self::API_CONTROLLER_PATH . $key, ['_store' => $storeId, '_secure' => true, '_nosid' => $noSid]);
    }

    public function getPushUrl($key, $storeId = null, $noSid = false)
    {
        return $this->_url->getUrl(self::PUSH_CONTROLLER_PATH . $key, ['_store' => $storeId, '_secure' => true, '_nosid' => $noSid]);
    }

    /**
     * Get order pending payment status
     */
    public function getPendingStatus($paymentMethodCode)
    {
        $status = $this->getPaymentConfigData($paymentMethodCode, 'pending_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PENDING_PAYMENT;
        }
        return $status;
    }

    /**
     * Get order processing status
     */
    public function getProcessingStatus($paymentMethodCode)
    {
        $status = $this->getPaymentConfigData($paymentMethodCode, 'processing_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PROCESSING;
        }
        return $status;
    }

    /**
     * Success process
     * [multi-method]
     *
     * Update succesful (paid) orders, send order email, create invoice
     * and send invoice email. Restore quote and clear cart.
     *
     * @param $order object
     * @param $note string Backend order history note
     * @param $transactionId string Transaction ID
     */
    public function processSuccess($order, $note, $transactionId)
    {
        $this->processCheck($order);
        $transactionId = (string)$transactionId;
        $order->getPayment()->setAdditionalInformation('transaction_id', $transactionId)
                            ->setLastTransId($transactionId)
                            ->save();

        // Set Total Paid & Due
        // (The invoice will do this.)
        // $amount = $order->getGrandTotal();
        // $order->setTotalPaid($amount);

        // Multi-method API
        $paymentMethodCode = $order->getPayment()->getMethod();
        
        // Set processing status
        $status = $this->getProcessingStatus($paymentMethodCode);
        
        if($transactionId){
			$note = $note . ' | Transaction Id : '.$transactionId;
		}
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
              ->setStatus($status)
              ->addStatusHistoryComment($note)
              ->setIsCustomerNotified(true)
              ->save();

        // Create invoice
        if ($this->getServiceConfigData('invoice_create')) {
            $this->processInvoice($order);
            $this->log->addDebug(__('Invoice created.'));
        }
    }

    /**
     * Create automatic invoice
     * [multi-method]
     *
     * @param $order object
     */
    public function processInvoice($order)
    {
        if (!$order->hasInvoices() && $order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            if ($invoice->getTotalQty() > 0) {
                $transactionId = $order->getPayment()->getTransactionId();
                $this->log->addDebug($transactionId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->setTransactionId($transactionId);
                $invoice->register();
                
                $transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                                        ->addObject($invoice)
                                        ->addObject($order)
                                        ->save();

                // Send invoice email
                if (!$invoice->getEmailSent() && $this->getServiceConfigData('invoice_email')) {
                    
                    // Nothing yet
                    return $this;
                }
                $invoice->save();
            }
        }
    }

    /**
     * Pending process
     * [multi-method]
     *
     * Update orders with explicit payment pending status. Restore quote.
     *
     * @param $order object
     * @param $note string Backend order history note
     * @param $transactionId string Transaction ID
     */
    public function processPending($order, $note, $transactionId)
    {
        $this->processCheck($order);
        $transactionId = (string)$transactionId;
        $order->getPayment()->setAdditionalInformation('transaction_id', $transactionId)
                            ->setLastTransId($transactionId)
                            ->save();

        // Multi-method API
        $paymentMethodCode = $order->getPayment()->getMethod();

		if($transactionId){
			$note = $note . ' | Transaction Id : '.$transactionId;
		}
		
        // Set pending_payment status
        $status = $this->getPendingStatus($paymentMethodCode);
        $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
              ->setStatus($status)
              ->addStatusHistoryComment($note)
              ->setIsCustomerNotified(true)
              ->save();
    }

    /**
     * Cancel process
     *
     * Update failed, cancelled, declined, rejected etc. orders. Cancel
     * the order and show user message. Restore quote.
     *
     * @param $order object
     * @param $note string Backend order history note
     * @param $transactionId string Transaction ID
     */
    public function processCancel($order, $transactionId)
    {
        $this->processCheck($order);
	    $transactionId = (string)$transactionId;
        $order->getPayment()->setAdditionalInformation('transaction_id', $transactionId)
                            ->setLastTransId($transactionId)
                            ->save();

        // Cancel order
        $order->cancel()->save();
    }

    /**
     * Check order state
     *
     * If the order state (not status) is already one of:
     * canceled, closed, holded or completed,
     * then we do not update the order status anymore.
     *
     * @param $order object
     */
    public function processCheck($order)
    {
        if ($order->getId()) {
            $state = $order->getState();
            switch ($state) {
                
                // Do not allow further updates; prevent double invoices
                case \Magento\Sales\Model\Order::STATE_HOLDED :
                case \Magento\Sales\Model\Order::STATE_CANCELED :
                case \Magento\Sales\Model\Order::STATE_CLOSED :
                case \Magento\Sales\Model\Order::STATE_COMPLETE :
                    
                    // Kill process
                    $this->log->addDebug(__('Payment already processed.'));
                    http_response_code(200);
                    throw new \Exception('Full stop.');
                    break;
                    
                // Allow updates
                case \Magento\Sales\Model\Order::STATE_NEW :
                case \Magento\Sales\Model\Order::STATE_PROCESSING :
                    break;
            }
        }
        else {
            
            // No order
            $this->log->addDebug(__('Order not found.'));
            http_response_code(200);
            throw new \Exception('Full stop.');
        }
    }

    /**
     * Restore cart
     */
    public function restoreCart($message = null)
    {
        $lastQuoteId = $this->checkoutSession->getLastQuoteId();
        if ($quote = $this->_getQuote()->loadByIdWithoutStore($lastQuoteId)) {
            $quote->setIsActive(true)
                  ->setReservedOrderId(null)
                  ->save();
            $this->checkoutSession->setQuoteId($lastQuoteId);
        }

		if(!$message){
			$message = __('Payment failed. Please try again.');
		}
		
        $this->messageManager->addError($message);
    }
}
