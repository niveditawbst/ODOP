<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Techelogy\Customization\Rewrite\Model;

use Magento\Framework\Exception\LocalizedException;
use \Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management object.
 */
class CouponManagement extends \Magento\Quote\Model\CouponManagement
{
    /**
     * @inheritDoc
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        
        // custom code to provide discount to subscribe customer at their first order 
        $customerId = $quote->getCustomerId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$discountCouponCode = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue('tact/general/discountCoupon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($customerId && $couponCode == $discountCouponCode){
			$checkSubscriber = $objectManager->create('\Magento\Newsletter\Model\Subscriber')->loadByCustomerId($customerId);
			if (!$checkSubscriber->isSubscribed()) {
				throw new CouldNotSaveException(__('You must be subscribe to use this coupon code.'));
			}
		}
        // end of custom code
        
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' .$e->getMessage()), $e);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                $e
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
        return true;
    }
}
