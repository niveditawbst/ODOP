<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tactconnect\PassEncryption\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 */
class RetriveSessionData
{
    /**
     * @var DepersonalizeChecker
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $backendSession;

	/**
     * @var $creationStudioParams
     */
    protected $creationStudioParams;

    /**
     * @var $productParams
     */
    protected $productParams;
    protected $_customerCreateEncKey;


    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->customerSession = $customerSession;
        $this->backendSession = $backendSession;
        $this->depersonalizeChecker = $depersonalizeChecker;
    }

    
	/**
	 * Before generate Xml
	 *
	 * @param \Magento\Framework\View\LayoutInterface $subject
	 * @return array
	 */
	public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
	{
	    if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
	    	$customerCreateEncKey = $customerLoginEncKey = $frontendChangePasswordEncKey = $frontendResetPasswordEncKey = $backendLoginEncKey = $backendResetPasswordEncKey = null;
	        $customerCreateEncKey = $this->customerSession->getCustomerCreateEncryptionKey();
	        $customerLoginEncKey = $this->customerSession->getCustomerLoginEncryptionKey();
	        $frontendChangePasswordEncKey = $this->customerSession->getFrontendChangePasswordEncryptionKey();
	        $frontendResetPasswordEncKey = $this->customerSession->getFrontendResetPasswordEncryptionKey();
	        //backend
	        $backendLoginEncKey = $this->backendSession->getBackendLoginEncryptionKey();
	        $backendResetPasswordEncKey = $this->backendSession->getBackendResetPasswordEncryptionKey();
	        
	        if(isset($customerCreateEncKey) && !empty($customerCreateEncKey))
	        {
	        	$this->_customerCreateEncKey = $customerCreateEncKey;
	        }
	        if(isset($customerLoginEncKey) && !empty($customerLoginEncKey))
	        {
	        	$this->_customerLoginEncKey = $customerLoginEncKey;
	        }
	        if(isset($frontendChangePasswordEncKey) && !empty($frontendChangePasswordEncKey))
	        {
	        	$this->_frontendChangePasswordEncKey = $frontendChangePasswordEncKey;
	        }
	        if(isset($frontendResetPasswordEncKey) && !empty($frontendResetPasswordEncKey))
	        {
	        	$this->_frontendResetPasswordEncKey = $frontendResetPasswordEncKey;
	        }
	        if(isset($backendLoginEncKey) && !empty($backendLoginEncKey))
	        {
	        	$this->_backendLoginEncKey = $backendLoginEncKey;
	        }
	        if(isset($backendResetPasswordEncKey) && !empty($backendResetPasswordEncKey))
	        {
	        	$this->_backendResetPasswordEncKey = $backendResetPasswordEncKey;
	        }
	    }
	    return [];
	}

    /**
	 * After generate Xml
	 *
	 * @param \Magento\Framework\View\LayoutInterface $subject
	 * @param \Magento\Framework\View\LayoutInterface $result
	 * @return \Magento\Framework\View\LayoutInterface
	 */
	public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
	{
	    if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
	        $this->customerSession->setCustomerCreateEncryptionKey($this->_customerCreateEncKey);
	        $this->customerSession->setCustomerLoginEncryptionKey($this->_customerLoginEncKey);
	        $this->customerSession->setFrontendChangePasswordEncryptionKey($this->_frontendChangePasswordEncKey);
	        $this->customerSession->setFrontendResetPasswordEncryptionKey($this->_frontendResetPasswordEncKey);
	        //backend
	        $this->backendSession->setBackendLoginEncryptionKey($this->_backendLoginEncKey);
	        $this->backendSession->setBackendResetPasswordEncryptionKey($this->_backendResetPasswordEncKey);
	    }
	    return $result;
	}
}
