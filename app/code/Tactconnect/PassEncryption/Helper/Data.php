<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tactconnect\PassEncryption\Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
	
    public function __construct(
    	Context $context,
    	\Magento\Customer\Model\Session $customerSession,
    	\Magento\Framework\Math\Random $mathRandom,
        \Magento\Backend\Model\Session $backendSession
    )
    {
    	$this->customerSession = $customerSession;
    	$this->mathRandom = $mathRandom;
        $this->backendSession = $backendSession;
    	parent::__construct($context);
    }

    public function getCustomerSession()
    {
    	return $this->customerSession;
    }
    public function getRandomString($length,  $chars = null)
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }
    public function getBackendSession()
    {
        return $this->backendSession;
    }

}