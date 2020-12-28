<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;

class SalesOrderShipmentAfter implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
        $shipment = $observer->getEvent()->getShipment();
        $orderObj = $shipment->getOrder();
                
		$custname = $orderObj->getCustomerName();
		$incrementId = $orderObj->getIncrementId();
		$telNo = $orderObj->getShippingAddress()->getTelephone();
		$customerTelephone = [];
		
		$tracksCollection = $shipment->getTracksCollection();
		$trackNumber = null;
		$carrierName = null;
		foreach ($tracksCollection->getItems() as $track) {
			$trackNumber = $track->getTrackNumber();
			$carrierName = $track->getTitle();
		}
            
     		
     	$message = 'Hi '.$custname.', Your Order has been shipped successfully. ';
     	if($carrierName){
			$message = $message . 'Shipment carrier is '. $carrierName . '. ';
		}
		
     	if($trackNumber){
			$message = $message . 'Tracking number: '. $trackNumber . '.';
		}
		
		$k = 0;
		$customerTelephone[$k]['pNo'] = $telNo;
		$customerTelephone[$k]['message'] = $message;
		$k++;
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
		
		//~ echo '<pre>####';
		//~ print_r($customerTelephone);
		//~ exit;	
	 
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
