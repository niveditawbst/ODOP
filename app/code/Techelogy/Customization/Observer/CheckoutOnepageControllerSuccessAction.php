<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutOnepageControllerSuccessAction implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

     $orderIds = $observer->getEvent()->getOrderIds();
     $customerTelephone = [];
     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
     $k = 0;
     foreach($orderIds as $key => $orderId){
			$orderObj = $objectManager->create('Magento\Sales\Model\OrderFactory')->create()->load($orderId);
		
			$custname = $orderObj->getCustomerName();
			$incrementId = $orderObj->getIncrementId();
			
			$message = 'Hi '.$custname.', Your Order has been successfully placed at ODOP Mart. Your Order No is '.$incrementId.'.';
			$customerTelephone[$k]['pNo'] = $orderObj->getShippingAddress()->getTelephone();
			$customerTelephone[$k]['message'] = $message;
			$k++;
	  }       
	  
	  // create bcc SMS
	  $smsArray = [];
	  $smsCopyNo = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue('tact/general/sendsmscopy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	  $enableSMS = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue('tact/general/enable_sms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	  if($smsCopyNo){
		  $smsArray = explode(',', $smsCopyNo);
	  }
		
	  if(count($smsArray) > 0){
		  foreach($smsArray as $key => $val){
			 $customerTelephone[$k]['pNo'] = $val;
			 $customerTelephone[$k]['message'] = $message;
			 $k++;
		  }
	  }	
	 
	 //	send SMS 
	 if(count($customerTelephone) > 0 && $enableSMS){
		try{
			$objectManager->create('Techelogy\Customization\Helper\Data')->sendSMS($customerTelephone);
		}catch(\Exception $e){
			return;
		}
	 }

    }

}
?>
