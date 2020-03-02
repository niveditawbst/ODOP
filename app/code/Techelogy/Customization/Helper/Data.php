<?php
/**
 * Copyright © 2015 Techelogy . All rights reserved.
 */
namespace Techelogy\Customization\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(\Magento\Framework\App\Helper\Context $context
	) {
		parent::__construct($context);
	}
	
	public function sendSMS($contactNo){
		if(is_array($contactNo) && count($contactNo) > 0){
		 
		 	$headers = [
				'Auth-Token:sms00i3es34au389sde3okpd-76de5fsdsfew3c565c-z45dfdsf32dfs4rt545-2fdgrp90so9sd3zcd83-b69878tjs43xze8s',
				'Content-Type: application/json',
			];

			foreach($contactNo as $contact){
				
				$pNo = $contact['pNo'];
				$message = urlencode($contact['message']);
				
				$url = 'https://upsectswrapperweb01.azurewebsites.net/api/tsms/SEND_PROMOTIONAL_SPL?MobileNo='.$pNo.'&Message='.$message.'&Senderid=UPTOUR';
					
				$ch = curl_init();		
						
				//~ curl_setopt($ch, CURLOPT_URL, urlencode($url));
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
				curl_setopt( $ch,CURLOPT_POST, false );
				curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
				$result = curl_exec($ch);
				$error = curl_error($ch);
				curl_close( $ch );
			//~ echo '<pre>####';
			//~ print_r($result);
			//~ print_r($error);
			//~ print_r($json_objekat);
			//~ exit;
				
		  }
	  }
	   //~ exit;
	}
}
