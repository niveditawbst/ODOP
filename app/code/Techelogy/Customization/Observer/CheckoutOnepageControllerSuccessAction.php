<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;

class CheckoutOnepageControllerSuccessAction implements ObserverInterface
{
    public function __construct(
    	Renderer $addressRenderer,
    	PaymentHelper $paymentHelper
    )
    {
    	$this->addressRenderer = $addressRenderer;
    	$this->paymentHelper = $paymentHelper;
    }

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
	 //send email for exhibition product
	 if(!empty($orderIds))
	 {
	 	try{
	 		$scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
		    foreach($orderIds as $orderId)
		    {
		    	$onlyExhibitionProduct = 1;
		    	$hasExhibitionProduct = 0;
		    	$order = $objectManager->create('Magento\Sales\Model\OrderFactory')->create()->load($orderId);
		    	$items = $order->getAllVisibleItems();
		       foreach ($items as $item) {

		          $isExhibition = $item->getProduct()->getData('exhibition_product');

		          if(!$isExhibition)
		          {
		              $onlyExhibitionProduct = 0;
		          }
		          else
		          {
		          	$hasExhibitionProduct = 1;
		          }
		       }

		       if($hasExhibitionProduct && !$onlyExhibitionProduct)
		       {
		           //send order email
		       		$templateId = 4;
		       		$storeId = $order->getStoreId();
		       		$email = $scopeConfig->getValue('trans_email/ident_sales/email' , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		            $name  = $scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		                    
		            $sender = [
		                        'name' => $name,
		                        'email' => $email,
		                      ];
		            //$templateParams = ['order']
		            $templateParams = [
			            'order' => $order,
			            'billing' => $order->getBillingAddress(),
			            'payment_html' => $order->getPaymentHtml(),
			            'store' => $order->getStore(),
			            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
			            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
			            'created_at_formatted' => $order->getCreatedAtFormatted(2),
			            'order_data' => [
			                'customer_name' => $order->getCustomerName(),
			                'is_not_virtual' => $order->getIsNotVirtual(),
			                'email_customer_note' => $order->getEmailCustomerNote(),
			                'frontend_status_label' => $order->getFrontendStatusLabel()
			            ]
			        ];
		       	   $objectManager->create('Techelogy\Customization\Helper\Data')->sendCustomEmail($templateId, $storeId, $sender, [$order->getCustomerEmail()], $templateParams);
		       	   //$objectManager->create('Techelogy\Customization\Helper\Data')->sendCopyTo();
		       }
		    }
		}catch(\Exception $e){
			return;
		}
	 }


    }

    public function getFormattedShippingAddress($order)
   	{
   		return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
   	}

   	public function getFormattedBillingAddress($order)
   	{
   		return $this->addressRenderer->format($order->getBillingAddress(), 'html');
   	}

   	/**
     * Get payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml($order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStore()->getStoreId()
        );
    }

}
?>
