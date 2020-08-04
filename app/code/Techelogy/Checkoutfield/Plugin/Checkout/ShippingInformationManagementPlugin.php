<?php

namespace Techelogy\Checkoutfield\Plugin\Checkout;

class ShippingInformationManagementPlugin
{

    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
        //~ $customAttributes = $addressInformation->getShippingAddress()->getCustomAttributes();
        
        //~ echo '<pre>@@@@';
        //~ print_r($extAttributes);
        //~ print_r($customAttributes);
        //~ exit;
        $altMobileNumber = $extAttributes->getAlternateMobileNumber();
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->getShippingAddress()->setAlternateMobileNumber($altMobileNumber);//->save();
    }
}
