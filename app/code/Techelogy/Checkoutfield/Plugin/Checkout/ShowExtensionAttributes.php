<?php

namespace Techelogy\Checkoutfield\Plugin\Checkout;

class ShowExtensionAttributes
{
    public function afterGetFormattedAddress(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject, $result, $address
    ) {
		if($address && $address->getData('address_type') == 'shipping'){
			$altMobNumber = $address->getData('alternate_mobile_number');
			
			if($altMobNumber){
				return $result . '<br/>Alternate Tel No: '. $altMobNumber;
			}
		}
		return $result;
    }
}
