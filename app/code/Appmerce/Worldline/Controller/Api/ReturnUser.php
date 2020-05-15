<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Controller\Api;

class ReturnUser extends \Appmerce\Worldline\Controller\Worldline
{
    /**
     * Success payment
     *
     * @return void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        
        $storeId = isset($params['_store']) ? $params['_store'] : null;
        $merchantResponse = isset($params['merchantResponse']) ? $params['merchantResponse'] : null;
        $mid = isset($params['mid']) ? $params['mid'] : null;
        
        /** Test Data **/
        //~ $params['_store'] = 1;
        //~ $params['merchantResponse'] = 'C5158DDF181624E33DDB8A954B6348776271CAE1BC99C365785080B02AF982C71F03EC00294926707B1E894C404C27ECC309050AB8B032393C372A89F7EC5EC50018009B3A8ECD9539FEBBDCE27D5C63D6DCACB2E9F2F17164156B470B7A2B26CD09D94D59B92584424F40EDA92C1BDBA20736077DE7C243FC94308408DB053F';
        //~ $params['mid'] = 'WL0000000027698';
        //~ $secureKey = '6375b97b954b37f956966977e5753ee6';
        //~ $decryData = $this->decryptValue($params['merchantResponse'], $secureKey);
		//~ $resArray = explode('|',$decryData);
		//~ echo '<pre>##';
		//~ print_r($resArray);
		//~ exit;
		/** Test Data **/
			
			
        $secureKey = null;
        if($storeId){
			$secureKey = $this->getServiceConfigData('secret_key', $params['_store']);
		}
        
        if($secureKey){
			$decryData = $this->decryptValue($params['merchantResponse'], $secureKey);
			$resArray = explode('|',$decryData);
			$paymentStatus = $resArray[10];
			$messageStatus = $resArray[11];
			
			
			if($paymentStatus == 'F'){
				$this->failure($messageStatus);
			}else if($paymentStatus == 'S'){
				$this->success();
			}
		}
        
        $this->log->addDebug(json_encode((array)$params));

        //~ $fail = true;
        //~ if (isset($params['Data']) && isset($params['Seal'])) {
            //~ $data = $this->getDataBits($params['Data']);
            //~ $order = $this->_getOrder()->loadByIncrementId($data['orderId']);

            //~ if ($this->checkSeal($params, $order)) {
                //~ switch ($data['responseCode']) {
                    //~ case self::STATUS_SUCCESS :
                    //~ case self::STATUS_PENDING :
                        //~ $fail = false;
                        //~ $this->success();
                        //~ break;
                //~ }
            //~ }
        //~ }
        
        //~ if ($fail == true) {
            //~ $this->failure();
        //~ }
    }

    /**
     * Success response
     */
    public function success()
    {
        // Processed by push notification
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    /**
     * Failure response
     */
    public function failure($message = null)
    {
        // Canceled by push notification
        $this->restoreCart($message);
        $this->_redirect('checkout/cart', array('_secure' => true));
    }
    
    /**
	 *
	 * @param
	 *        	$inputVal
	 * @param
	 *        	$secureKey
	 * @return string
	 */
	function decryptValue($inputVal, $secureKey) {
		$key = '';
		for($i = 0; $i < strlen ( $secureKey ) - 1; $i += 2) {
			
			$key .= chr ( hexdec ( $secureKey [$i] . $secureKey [$i + 1] ) );
		}
		
		$encblock = '';
		for($i = 0; $i < strlen ( $inputVal ) - 1; $i += 2) {
			$encblock .= chr ( hexdec ( $inputVal [$i] . $inputVal [$i + 1] ) );
		}
		
		$decrypted_text = mcrypt_decrypt ( MCRYPT_RIJNDAEL_128, $key, $encblock, MCRYPT_MODE_ECB );
		
		return $decrypted_text;
	}

}
