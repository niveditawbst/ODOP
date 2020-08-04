<?php
namespace Techelogy\Checkoutfield\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{

   /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyService;
    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
      \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');
        
        $shippingAddressData = $quote->getShippingAddress()->getData();
        if (isset($shippingAddressData['alternate_mobile_number'])) {
            $order->getShippingAddress()->setAlternateMobileNumber($shippingAddressData['alternate_mobile_number']);
        }
        
      return $this;
    }

}
