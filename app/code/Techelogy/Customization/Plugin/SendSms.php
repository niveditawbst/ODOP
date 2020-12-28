<?php
namespace Techelogy\Customization\Plugin;

class SendSms
{
    public function afterExecute(\Magento\Sales\Controller\Adminhtml\Order\AddComment $subject, $result){
		//~ $params = $subject->getRequest()->getPost('history');
		$params = $subject->getRequest()->getParams();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$orderId = isset($params['order_id']) ? $params['order_id'] : null;
		$historyStatus = isset($params['history']['status']) ? $params['history']['status'] : null;
		$historyComment = isset($params['history']['comment']) ? $params['history']['comment'] : null;
		$notify = isset($params['history']['is_customer_notified']) ? $params['history']['is_customer_notified'] : false;
		$visible = isset($params['history']['is_visible_on_front']) ? $params['history']['is_visible_on_front'] : false;
		
		if($orderId && $historyStatus && $historyComment){
			if($historyStatus == 'delivered'){
				
				$orderObj = $objectManager->create('Magento\Sales\Model\OrderFactory')->create()->load($orderId);
				$custname = $orderObj->getCustomerName();
				$incrementId = $orderObj->getIncrementId();
				$telNo = $orderObj->getShippingAddress()->getTelephone();
				
				$customerTelephone = [];
					
				$message = 'Hi '.$custname.', Your Order with Id #'.$incrementId.' has been delivered successfully.';
				
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
						
						if($historyComment){
							$historyComment = $historyComment . ' | Sent SMS for order delivery.'; 
						}else{
							$historyComment = 'Sent SMS for order delivery.'; 
						}
						
						$history = $orderObj->addStatusHistoryComment($historyComment, $historyStatus);
						$history->setIsVisibleOnFront($visible);
						$history->setIsCustomerNotified($notify);
						$history->save();
                
					}catch(\Exception $e){
						return;
					}
				}
		
			}
		}
		return $result;
    }
}
