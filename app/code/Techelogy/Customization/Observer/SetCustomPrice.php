<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SetCustomPrice implements ObserverInterface
{
     public function execute(\Magento\Framework\Event\Observer $observer) {
		 
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$requestInterface = $objectManager->create('\Magento\Framework\App\RequestInterface');
		
		$productId = $observer->getProduct()->getId();
		$isExhibitionProduct = $observer->getProduct()->getData('exhibition_product');
		
		if($productId && $isExhibitionProduct){
			
			$exhibitionPrice = $requestInterface->getParam('exhibition_price');
			//~ $qty = $requestInterface->getParam('qty');
			//~ $finalPrice = $exhibitionPrice * $qty;
		
		
			$item = $observer->getEvent()->getData('quote_item');         
			$item = ( $item->getParentItem() ? $item->getParentItem() : $item );
			$price = $exhibitionPrice; //set your price here
			$item->setCustomPrice($price);
			$item->setOriginalCustomPrice($price);
			$item->getProduct()->setIsSuperMode(true);
		}
	}
}
?>
