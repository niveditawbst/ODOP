<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class DisablePaymentMethod implements ObserverInterface
{
	
	/**
     * RestrictPaymentMethodsObserver constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Cart $cart
    ){
        $this->_objectManager = $objectManager;
        $this->_cart = $cart;
    }
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
		//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
        // echo '==='.$quoteObj = $observer->getQuote()->getId();exit;
        $quoteObj = $this->_cart->getQuote();
        if($quoteObj)
        {

	        $quoteItems = $quoteObj->getAllVisibleItems();
	        $hasExhibitionProduct = false;
	        foreach($quoteItems as $quoteItem){
				$productId = $quoteItem->getProductId();
				$productObj = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection()
								->addAttributeToSelect('exhibition_product')
									->addAttributeToFilter('entity_id', $productId)
										->getFirstItem();
				$exhibitionProduct = $productObj->getData('exhibition_product');
				
				if($exhibitionProduct){
					$hasExhibitionProduct = true;
				}
			}
			
			$paymentMethodCode = $observer->getEvent()->getMethodInstance()->getCode();
	        $disablePaymentList = ['banktransfer', 'cashondelivery'];
	        if($hasExhibitionProduct && in_array($paymentMethodCode, $disablePaymentList)){
	            $checkResult = $observer->getEvent()->getResult();
	            $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
	        }
	    }
    }
}
?>
