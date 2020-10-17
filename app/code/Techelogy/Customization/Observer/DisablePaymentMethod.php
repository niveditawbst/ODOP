<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class DisablePaymentMethod implements ObserverInterface
{
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
        //~ echo '==='.$quoteObj = $observer->getQuote()->getId();exit;
        //~ $quoteItems = $quoteObj->getAllItems();
        $hasExhibitionProduct = true;
        //~ foreach($quoteItems as $quoteItem){
			//~ $productId = $quoteItem->getProductId();
			//~ $productObj = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->getCollection()
							//~ ->addAttributeToSelect('exhibition_product')
								//~ ->addAttributeToFilter('entity_id', $productId)
									//~ ->getFirstItem();
			//~ $exhibitionProduct = $productObj->getData('exhibition_product');
			
			//~ if($exhibitionProduct){
				//~ $hasExhibitionProduct = true;
			//~ }
		//~ }
		
		$paymentMethodCode = $observer->getEvent()->getMethodInstance()->getCode();
        $disablePaymentList = ['banktransfer', 'cashondelivery'];
        if($hasExhibitionProduct && in_array($paymentMethodCode, $disablePaymentList)){
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
        }
    }
}
?>
